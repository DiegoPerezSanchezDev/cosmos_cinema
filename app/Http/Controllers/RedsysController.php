<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Entrada;
use App\Models\Asiento;
use App\Models\AsientoEstado;
use App\Mail\EmailEntradas;
use App\Models\Fecha;
use App\Models\Hora;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Ssheduardo\Redsys\Facades\Redsys;
use Barryvdh\DomPDF\Facade\Pdf;
use Sermepa\Tpv\Tpv;
use Str;

class RedsysController extends Controller
{

    private ?Factura $factura = null;
    private $entradas = null;
    private ?array $asientos_ids = null;
    private ?array $rutas_pdf = null;
    private ?User $usuario = null;
    private ?string $ruta_pdf_factura = null;


    // Lógica de pago -> Petición y respuesta a Redsys
    public function redireccionar_redsys($pedido_id_redsys, $cantidad)
    {
        try {
            // Instanciar clase Tpv (se usará para construir el formulario que se enviará)
            $tpv = new Tpv();

            // Establecer variables de petición
            $tpv->setAmount($cantidad);
            $tpv->setOrder($pedido_id_redsys);
            $tpv->setMerchantcode(config('redsys.merchantcode'));
            $tpv->setCurrency(config('redsys.moneda'));
            $tpv->setTransactiontype(config('redsys.tipo_transaccion'));
            $tpv->setTerminal(config('redsys.terminal'));
            $tpv->setNotification(route('redsys_notification'));
            $tpv->setUrlOk(route('redsys_ok'));
            $tpv->setUrlKo(route('redsys_ko'));
            $tpv->setEnvironment(config('redsys.enviroment'));
            $tpv->setMethod(config('redsys.metodo_pago'));
            $tpv->setTitular(config('redsys.titular'));
            $tpv->setProductDescription(config('redsys.description'));
            $tpv->setTradeName(config('redsys.tradename'));
            $tpv->setVersion('HMAC_SHA256_V1');

            // Generar firma mediante la clave
            $signature = $tpv->generateMerchantSignature(config('redsys.key'));
            $tpv->setMerchantSignature($signature);
            
            // Crear formulario que se auto envía
            return $tpv->executeRedirection(true);
        } catch (\Exception $e) {
            Log::error('Error al preparar datos para Redsys: ' . $e->getMessage(), [
                'exception_class' => get_class($e),
                'exception_file' => $e->getFile(),
                'exception_line' => $e->getLine(),
                'order_id' => $pedido_id_redsys,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }

        return true;
    }


    public function handle_notification(Request $request)
    {

        Log::info('Redsys Notification Received:', $request->all());

        // Recuperar clave
        $key = config('redsys.key');

        // Iniciar transacción
        DB::beginTransaction();

        try {
            // La librería usa los datos del $request directamente para Redsys::check y Redsys::getMerchantParameters
            if (!Redsys::check($key, $request->all())) {
                Log::warning('Redsys Notification: Signature Mismatch.', ['request_data' => $request->all()]);
                return response('KO - Signature Mismatch', 200)->header('Content-Type', 'text/plain');
            }

            // Recuperar la respuesta
            $parameters = Redsys::getMerchantParameters($request->input('Ds_MerchantParameters'));

            // Recuperar los parámetros de la respuesta
            $dsResponse = intval($parameters["Ds_Response"]);
            $dsOrder = $parameters["Ds_Order"];
            $dsAmount = $parameters["Ds_Amount"];
            $dsAuthorisationCode = $parameters["Ds_AuthorisationCode"];

            Log::info('Redsys Notification Parameters Decoded:', $parameters);

            // 1. Recuperar la factura a través del número de pedido
            $this->factura = Factura::where('pedido_redsys_id', $dsOrder)
                                    ->first();

            // Si no se recupera la factura se lanza un error
            if (!$this->factura) {
                Log::error("Redsys Notification: Factura con pedido_redsys_id {$dsOrder} no encontrada.");
                return response('KO - Order not found', 200)->header('Content-Type', 'text/plain');
            }

            // 2. Comprobar que la factura no está ya pagada
            if ($this->factura->estado === 'pagado' || $this->factura->estado === 'fallido') {
                Log::info("Redsys Notification: Factura {$this->factura->id_factura} (Redsys Order: {$dsOrder}) ya está pagada.");
                return response('OK', 200)->header('Content-Type', 'text/plain');
            }

            // 3. Verificar importe
            if ((float) $this->factura->monto_total * 100 !== (float) $dsAmount) {
                Log::error("Redsys Notification: Discrepancia de importe para {$dsOrder}. BD: ".round($this->factura->monto_total * 100).", Redsys: ".$dsAmount);
                $this->factura->estado = 'Error Importe';
                $this->factura->save();

                DB::commit();

                 // OK para Redsys, pero error interno
                return response('OK', 200)->header('Content-Type', 'text/plain');
            }

            // 4. Comprobar el código de respuesta de Redsys
            if ($dsResponse >= 0 && $dsResponse <= 99) {
                // Pago aprobado
                Log::info("Redsys Notification: Pago APROBADO para Factura {$this->factura->id_factura}, Redsys Order: {$dsOrder}, Redsys Resp: {$dsResponse}");
                
                // 1. Cambiar estado y datos de factura
                $this->factura->estado = 'pagado';
                $this->factura->codigo_autorizacion_redsys = $dsAuthorisationCode;
                $this->factura->fecha_pago = now();
                $this->factura->save();

                // Guardamos el estado de la factura pagada (por si hay un error más adelante)
                DB::commit();
                DB::beginTransaction();
                
                // 2. Recuperar entradas
                $this->entradas = Entrada::where('factura_id', $this->factura->id_factura)->get();

                // 3. Actualizar el estado de las entradas de 'pendiente' a 'pagado'
                if (!$this->actualizar_entradas()) {
                    DB::rollBack();
                    Log::info("Error al actualizar el estado de las entradas: " . $this->entradas);
                    return response('OK', 200)->header('Content-Type', 'text/plain');
                }

                if (!$this->crear_pdfFacturas()) {
                    DB::rollBack();
                    Log::info("Error al actualizar el estado de las entradas: " . $this->entradas);
                    return response('OK', 200)->header('Content-Type', 'text/plain');
                }

                // 4. Actualizar el estado de las entradas de 'reservado' a 'ocupado'
                if (!$this->actualizar_asientos()) {
                    DB::rollBack();
                    Log::info("Error al actualizar el estado de los asientos: " . $this->asientos_ids);
                    return response('OK', 200)->header('Content-Type', 'text/plain');
                }
                
                // 5. Enviar correo de confirmación
                if (!$this->enviar_correo()) {
                    DB::rollBack();
                    Log::info("Error al enviar el correo de confiramción: " . $this->factura->id_factura);
                    return response('OK', 200)->header('Content-Type', 'text/plain');
                }

                Log::info("Pago APROBADO y procesado para Factura {$this->factura->id_factura}, Redsys Order: {$dsOrder}.");
            } else {
                // Pago denegado o error
                $this->factura->estado = 'fallido';
                $this->factura->save();

                // 1. Actualizar entradas a 'cancelada'
                Entrada::where('factura_id', $this->factura->id_factura)
                    ->where('estado', 'pendiente_pago')
                    ->update(['estado' => 'cancelada']);

                // 2. Volver asientos de 'reservado' a 'disponible'
                $this->entradas = Entrada::where('factura_id', $this->factura->id_factura)->get();
                $this->asientos_ids = $this->entradas->pluck('asiento_id')->unique()->toArray();


                if (!empty($this->asientos_ids)) {
                    // Recuperar estados
                    $estado_disponible = AsientoEstado::where('estado', 'disponible')->first();
                    $estado_reservado = AsientoEstado::where('estado', 'reservado')->first();

                    // Cambiar estado a todos los asientos de 'reservado' a 'disponible'
                    if ($estado_disponible && $estado_reservado) {
                        Asiento::whereIn('id_asiento', $this->asientos_ids)
                               ->where('estado', $estado_reservado->id) // Solo los que esta factura reservó
                            ->update(['estado' => $estado_disponible->id]);
                        Log::info("Asientos liberados para factura fallida {$this->factura->id_factura}.");
                    } else {
                        Log::error("No se encontraron estados 'disponible' o 'reservado' para liberar asientos. Factura: {$this->factura->id_factura}");
                    }
                }

                Log::warning("Pago DENEGADO/ERROR para Factura {$this->factura->id_factura}, Redsys Order: {$dsOrder}, Redsys Resp: {$dsResponse}.");
            }

            // Confirmar los cambios
            DB::commit();
            
            // Notificar a Redsys que todo OK
            return response('OK', 200)->header('Content-Type', 'text/plain'); 

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Redsys Notification General Exception: ' . $e->getMessage(), ['exception' => $e, 'request_data' => $request->all()]);
            return response('KO - General Error', 200)->header('Content-Type', 'text/plain');
        }
    }



    ////////////////////////////// Respuestas de Redsys //////////////////////////////

    // Función de respuesta OK
    public function handle_ok(Request $request) {
        Log::info('Redsys OK Redirección para usuario:', $request->all());

        return redirect('/')->with('success', '¡Pago realizado con éxito!');
    }


    // Función de respuesta KO
    public function handle_ko(Request $request) {
        Log::info('Redsys OK Redirección para usuario:', $request->all());

        return redirect('/')->with('success', 'Ha habido un fallo en el proceso pago. Por favor, inténtelo de nuevo o contacta con soporte.');
    }










    // Funciones de actualización de estados

    private function actualizar_entradas(): bool
    {
        foreach ($this->entradas as $entrada) {
            $entrada->estado = 'pagado';
            $this->asientos_ids[] = $entrada->asiento_id;

            // 2. Generar PDF para esta entrada
            $ruta_pdf = $this->crear_pdf($entrada);
            if ($ruta_pdf) {
                $entrada->ruta_pdf = $ruta_pdf;

                // Recuperar las ruta absolutas de los PDF para luego enviarlos por email
                $this->rutas_pdf[] = Storage::disk('public')->path($ruta_pdf);
            } else {
                Log::error("Fallo al generar PDF para entrada ID {$entrada->id_entrada} de factura {$this->factura->id_factura}");
                return false;
            }
            $entrada->save();
        }

        return true;
    }


    // Crear un pdf por cada entrada
    private function crear_pdf(Entrada &$entrada): ?string {
        try {
            if (!$entrada) {
                return null;
            }

            // Crear objeto con datos de empresa
            $empresa = (object) [
                'nombre_legal' => config('company.name', 'Cosmos Cinema (Test)'),
                'cif' => config('company.cif', 'B99999999'),
            ];

            // Recuperar fecha
                $fecha_entrada = Fecha::find($entrada->fecha);
                if (!$fecha_entrada) {
                    Log::error("No se pudo guardar recuperar la fecha de la entrada: " . $entrada->fecha);
                    return null;
                }

                // Recuperar hora
                $hora_entrada = Hora::find($entrada->hora);
                if (!$hora_entrada) {
                    Log::error("No se pudo guardar recuperar la hora de la entrada: " . $entrada->hora);
                    return null;
                }

                $entrada->hora = $hora_entrada->hora;
            
            // Cargar la vista Blade para la entrada
            $pdf = PDF::loadView('pdf.entrada_cine', compact('entrada', 'empresa', 'fecha_entrada', 'hora_entrada'));

            // Se crea un nombre de archivo único
            $nombre_archivo = 'entrada-' . $entrada->id_entrada . '-' . Str::random(10) . '.pdf';

            // Se crea la ruta relativa
            $ruta_relativa = 'entradas_pdf/' . $nombre_archivo;

            // Se guarda el pdf
            $exito_guardado = Storage::disk('public')->put($ruta_relativa, $pdf->output());

            if (!$exito_guardado) {
                Log::error("No se pudo guardar el PDF en el disco 'public': " . $ruta_relativa);
                return null;
            }

            return $ruta_relativa;

        } catch (\Exception $e) {
            Log::error("Error generando PDF para entrada ID {$entrada->id_entrada}: " . $e->getMessage(), ['exception' => $e]);
            // No setear $this->ultimoError aquí directamente, dejar que el método llamador lo haga
            // si este fallo es crítico para el proceso general.
            return null;
        }
    }

    private function crear_pdfFacturas(): ?bool {
        try {
            if (!$this->factura) {
                return false;
            }

            $factura = $this->factura;
            $entradas = $this->entradas;
            $this->usuario = User::where('id', $this->factura->id_user)->first();
            $usuario = $this->usuario;

            // Cargar la vista Blade para la entrada
            $pdf = PDF::loadView('emails.factura_entrada', compact('entradas', 'factura', 'usuario'));

            // Se crea un nombre de archivo único
            $nombre_archivo = 'factura-' . $factura->id_factura . '-' . Str::random(10) . '.pdf';

            // Se crea la ruta relativa
            $ruta_relativa_factura = 'factura_pdf/' . $nombre_archivo;

            // Se guarda el pdf
            $exito_guardado = Storage::disk('public')->put($ruta_relativa_factura, $pdf->output());

            if (!$exito_guardado) {
                Log::error("No se pudo guardar el PDF en el disco 'public': " . $ruta_relativa_factura);
                return null;
            }

            $this->ruta_pdf_factura= Storage::disk('public')->path($ruta_relativa_factura);

            return true;

        } catch (\Exception $e) {
            Log::error("Error generando PDF para entrada ID {$factura->id_factura}: " . $e->getMessage(), ['exception' => $e]);
            // No setear $this->ultimoError aquí directamente, dejar que el método llamador lo haga
            // si este fallo es crítico para el proceso general.
            return false;
        }
    }


    private function actualizar_asientos(): bool {
        if (empty($this->asientos_ids)) {
            return false;
        }

        // Cambiar estado de cada asiento
        // Recuperar estados
        $estado_ocupado = AsientoEstado::where('estado', 'ocupado')->first();
        $estado_reservado = AsientoEstado::where('estado', 'reservado')->first();

        // Cambiar estado del asiento
        if ($estado_ocupado && $estado_reservado) {
            Asiento::whereIn('id_asiento', array_unique($this->asientos_ids))
                    ->where('estado', $estado_reservado->id)
                    ->update(['estado' => $estado_ocupado->id]);
            Log::info("Asientos marcados como ocupados para factura {$this->factura->id_factura}.");
        } else {
            Log::error("No se encontró el estado 'ocupado' o 'reservado' para asientos. Factura: {$this->factura->id_factura}");
            return false;
        }

    return true;
    }


    // Enviar correo al usuario de confirmación de compra, con las entradas adjuntadas
    private function enviar_correo(): bool {
        
        // Recuperar email
        $email_destino = $this->factura["titular_email"];
        $email_usuario = null;
        

        // Comprobar si es usuario registrado o invitado
        if ($this->usuario) {
            $email_usuario = $this->usuario;
        } else {
            // Crear un objeto de "mentira" User o pasar los datos del invitado al Mailable
            $email_usuario = new User(['name' => $this->factura->titular ?? 'Cliente']);
        }

        // Si no hay email de destino se lanza un error
        if (is_null($email_destino)) {
            Log::warning("No se pudo determinar el email de destino para la factura {$this->factura->id_factura}. Correo no enviado.");
            return false;
        }

        // Si no hay pdf generado/s, se lanza un error
        if (empty($this->rutas_pdf)) {
            Log::warning("No se enviará correo para factura {$this->factura->id_factura}: No hay PDFs para adjuntar.");
            return false;
        }

        try {
            // Crear instancia de Mailable (EmailEntradas)
            $correo_confirmacion = new EmailEntradas($email_usuario, $this->factura, $this->rutas_pdf, $this->ruta_pdf_factura);

            // Enviar el correo con Mail. También lo ponemos en cola
            // Al ponerlo en cola, la aplicación seguirá funcionando (para el usuario) mientras por detrás se manda el email
            Mail::to($email_destino)->send($correo_confirmacion);

            Log::info("Correo de confirmación de compra enviado a: " . $email_destino . " con " . count($this->rutas_pdf) . " entradas adjuntas.");
        } catch (\Exception $e) {
            Log::error("Error critico enviando correo de confirmación para factura {$this->factura->id_factura}: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return false;
        }

        return true;
    } 
}
