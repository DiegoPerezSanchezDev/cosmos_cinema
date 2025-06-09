<?php

namespace App\Http\Controllers;

use App\Models\Asiento;
use App\Models\AsientoEstado;
use App\Models\Entrada;
use App\Models\Factura;
use App\Models\Pelicula;
use App\Models\Sala;
use App\Models\SesionPelicula;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Str;
use App\Http\Controllers\RedsysController;

class GenerarEntrada
{
    private array $datos_validados;

    private ?\Illuminate\Database\Eloquent\Collection $asientos = null;
    private ?SesionPelicula $sesion = null;
    private ?User $usuario = null;
    private ?Factura $factura = null;
    private ?array $entradas = null;
    private ?array $respuesta = null;
    private ?string $redsys_form_html = null;

    public ?string $ultimoError = null;

    public function generar_entrada($datos_validados): array {
        // Recuperar datos
        $this->datos_validados = $datos_validados;

        // Se resetea el ultimoError por si hay que devolverlo
        $this->ultimoError = null;
        $this->respuesta = [
            'status' => 'error',
            'data' => '',
            'message' => 'No se han generado datos'
        ];

        // Se inicia una nueva transacción
        DB::beginTransaction();

        try {

            if (!$this->recuperar_asientos()) {
                DB::rollBack();
                $this->respuesta["message"] = $this->ultimoError;
                return $this->respuesta;
            }

            if (!$this->recuperar_sesion()) {
                DB::rollBack();
                $this->respuesta["message"] = $this->ultimoError;
                return $this->respuesta;
            }
            
            if (isset($datos_validados["usuario_id"])) {
                if (!$this->recuperar_usuario()) {
                    DB::rollBack();
                    $this->respuesta["message"] = $this->ultimoError;
                    return $this->respuesta;
                }
            }

            if (!$this->generar_factura_pendiente()) {
                DB::rollBack();
                $this->respuesta["message"] = $this->ultimoError;
                return $this->respuesta;
            }

            if (!$this->generar_entradas_pendientes()) {
                DB::rollBack();
                $this->respuesta["message"] = $this->ultimoError;
                return $this->respuesta;
            }

            if (!$this->actualizar_asientos_reservados()) {
                DB::rollBack();
                $this->respuesta["message"] = $this->ultimoError;
                return $this->respuesta;
            }

            if (!$this->preparar_pago_redsys()) {
                DB::rollBack();
                $this->respuesta["message"] = $this->ultimoError;
                return $this->respuesta;
            }

            // Generar respuesta para procesar luego los datos generados
            $this->respuesta = [
                'status' => 'success',
                'data' => $this->redsys_form_html,
                'message' => 'Pago exitoso. Generadas entradas y factura. Asientos actualizados'
            ];

            DB::commit();
            return $this->respuesta;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error crítico al generar entradas: ' . $e->getMessage(), [
                'datos' => $this->datos_validados,
                'exception_trace' => $e->getTraceAsString()
            ]);
            $this->respuesta["message"] = 'Ocurrió un error inesperado durante el proceso. Por favor, contacta con soporte.';
            return $this->respuesta;
        }
    }

    private function recuperar_asientos(): bool {
        try {
            // Recuperar el id de Disponible
            $estado = AsientoEstado::where('estado', 'disponible')->first();

            // Si no se puede recuperar lanzamos un error
            if (!$estado) {
                $this->ultimoError = "No se pudo encontrar la definición del estado 'disponible'. Por favor, verifica la configuración del sistema.";
                Log::error($this->ultimoError);
                return false;
            }

            // Recuperar asientos por id
            $this->asientos = Asiento::whereIn('id_asiento', $this->datos_validados['asiento'])
                                        ->where('estado', $estado->id)
                                           ->lockForUpdate()        // Bloquear esos asientos hasta que termine la transacción
                                        ->get();

            // Comprobar que se han recuperado la cantidad de asientos correcta
            if ($this->asientos->count() !== count($this->datos_validados['asiento'])) {
                $this->ultimoError = "Algunos asientos seleccionados ya no están disponibles. Por favor, inténtelo de nuevo más tarde"; 
                Log::warning($this->ultimoError, ['solicitados' => $this->datos_validados['asiento'], 'encontrados' => collect($this->asientos)->pluck('id_asiento')->toArray()]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error recuperando asientos: " . $e->getMessage());
            $this->ultimoError = "Error al verificar la disponibilidad de los asientos.";
            return false;
        }
    }


    private function recuperar_sesion(): bool {
        try {
            // Recuperar asientos por id
            $this->sesion = SesionPelicula::find($this->datos_validados["sesion_id"]);

            // Comprobar que se han recuperado la sesión
            if (!$this->sesion) {
                $this->ultimoError = 'La sesión de película seleccionada ya no es válida o no existe.';
                Log::warning($this->ultimoError, [
                    'sesion_id_solicitada' => $this->datos_validados['sesion_id']
                ]);
                return false;
            }

            // Comprobar que la sesión está activa
            if (!$this->sesion->activa) {
                $this->ultimoError = 'La sesión de película seleccionada ya no está activa.';
                Log::warning($this->ultimoError, [
                    'sesion_id_solicitada' => $this->sesion->id,
                    'sesion_estado' => $this->sesion->activa
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error recuperando la sesión ID {$this->datos_validados['sesion_id']}: " . $e->getMessage());
            $this->ultimoError = "Error al cargar los datos de la sesión. Por favor, inténtalo más tarde.";
            return false;
        }
    }


    private function recuperar_usuario(): bool {

        
        // Comprobar si el usuario es invitado. Si es invitado usuario será null
        if (!isset($this->datos_validados['usuario_id']) || is_null($this->datos_validados['usuario_id'])) {
            $this->usuario = null;
            return true;
        }

        try {
            // Recuperar usuario por id
            $this->usuario = User::find($this->datos_validados["usuario_id"]);

            // Comprobar que se ha recuperado el usuario
            if (!$this->usuario) {
                $this->ultimoError = 'La información del usuario asociado a la compra no es válida o el usuario no existe.';
                Log::warning($this->ultimoError, [
                    'usuario_id_solicitado' => $this->datos_validados['usuario_id']
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error recuperando el usuario ID {$this->datos_validados['usuario_id']}: " . $e->getMessage());
            $this->ultimoError = "Error al cargar los datos del usuario. Por favor, inténtalo más tarde.";
            return false;
        }
    }


    private function generar_factura_pendiente(): bool {

        // Se generan el número de factura y el id de pedido a redsys
        $num_factura = 'ORD-' . time() . '-' . rand(1000, 9999);
        $pedido_redsys_id = substr(str_replace('-', '', $num_factura), 5, 17);

        // Recuperar el email (invitado o usuario)
        $titular_email = "";
        if ($this->usuario) {
            $titular_email = $this->usuario->email;
        } else {
            $titular_email = $this->datos_validados["email_invitado"];
        }

        try {
            // TODO -> Mirar a ver si se puede arreglar el id_impuesto
            // Se crea la factura con los datos recuperados
            $this->factura = Factura::create([
                'monto_total' => $this->datos_validados["precio_final"],
                'titular_email' => $titular_email,
                'num_factura' => $num_factura,
                'estado' => 'pendiente',
                'pedido_redsys_id' => $pedido_redsys_id,
                'id_user' => $this->usuario ? $this->usuario->id : null, 
                'id_impuesto' => 1,
            ]);

            // Si no se genera correctamente, se lanza un error
            if (!$this->factura) {
                $this->ultimoError = "Error al crear el registro de la factura.";
                Log::error($this->ultimoError);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error generando la factura: " . $e->getMessage());
            $this->ultimoError = "Error al generar la factura. Por favor, inténtalo más tarde.";
            return false;
        }
    }


    private function generar_entradas_pendientes(): bool {
        try {
            // Recuperar número de sala
            $sala = Sala::find($this->sesion->id_sala);

            if (!$sala) {
                $this->ultimoError = 'Hubo un error en la generación de entradas. Por favor, inténtelo más tarde.';
                Log::warning($this->ultimoError, [
                    'sala_id' => $this->sesion->id_sala, 'sala' => $sala
                ]);
                return false;
            }

            // Recuperar título de película
            $pelicula = Pelicula::find($this->sesion->id_pelicula);

            if (!$pelicula) {
                $this->ultimoError = 'Hubo un error en la generación de entradas. Por favor, inténtelo más tarde.';
                Log::warning($this->ultimoError, [
                    'pelicula_id' => $this->sesion->id_pelicula, 'pelicula' => $pelicula
                ]);
                return false;
            }
    
            // Se genera una entrada por cada asiento distinto que hay
            foreach ($this->asientos as $asiento) {
                // Generar un código QR por cada entrada
                $codigo_qr = $this->generar_codigo_qr();

                // Si no se genera correctamente, se lanza un error
                if (!$codigo_qr) {
                    $this->ultimoError = 'Hubo un error en la generación de entradas. Por favor, inténtelo más tarde.';
                    Log::warning($this->ultimoError, [
                        'codigo_qr_generado' => $codigo_qr
                    ]);
                    return false;
                }

                // Calcular precios y descuentos de cada entrada
                $precio_total = 10;
                $porcentaje_descuento = $this->datos_validados['precio_descuento'];
                $precio_final = $precio_total * (1 - ($porcentaje_descuento / 100));

                // TODO -> Ver que se puede hacer con tipo_entrada
                // TODO -> Ver si se puede arreglar id_sala para que sea un sala_numero o algo así
                // TODO -> Ver lo del los precios de las entradas (sacarlo de BBDD)
                $entrada = Entrada::create([
                    'codigo_qr' => $codigo_qr,
                    'ruta_pdf' => "",
                    'estado' => 'pendiente_pago',
                    'precio_total' => $precio_total,
                    'descuento' => $porcentaje_descuento,
                    'precio_final' => $precio_final,
                    'sala' => $sala->id_sala,
                    'sala_id' => $this->sesion->id_sala,
                    'poster_ruta' => $pelicula->poster_ruta,
                    'pelicula_titulo' => $pelicula->titulo,
                    'pelicula_id' => $this->sesion->id_pelicula,
                    'hora' => $this->sesion->hora,
                    'fecha' => $this->sesion->fecha,
                    'asiento_id' => $asiento->id_asiento,
                    'asiento_fila' => $asiento->fila,
                    'asiento_columna' => $asiento->columna,
                    'usuario_id' => $this->usuario ? $this->usuario->id : null,
                    'factura_id' => $this->factura->id_factura,
                    'tipo_entrada' => 1,
                ]);

                if (!$entrada) {
                    $this->ultimoError = 'Hubo un error en la generación de entradas. Por favor, inténtelo más tarde.';
                    Log::warning($this->ultimoError, [
                        'entrada' => $entrada
                    ]);
                    return false;
                };

                $this->entradas[] = $entrada;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error generando las entradas: " . $e->getMessage());
            $this->ultimoError = "Error al generar las entradas. Por favor, inténtalo más tarde.";
            return false;
        }
    }


    private function generar_codigo_qr(): string {
        // Se utiliza una herramienta Str para generar un String de 128 bits único (prácticamente imposible repetir)
        $codigo_unico = 'ENTRADA-' . Str::uuid()->toString();
        
        return $codigo_unico;
    }


    private function actualizar_asientos_reservados(): bool {
        try {

            // Comprobar que hay asientos
            if ($this->asientos->isEmpty()) {
                Log::info("No hay asientos para actualizar su estado.", ['datos_validados' => $this->datos_validados]);
                $this->ultimoError = "Error al actualizar los asientos. Por favor, inténtalo más tarde.";
                return false;
            }

            // Se guardan los ids de los asientos en un array
            $asientos_id = $this->asientos->pluck('id_asiento')->toArray();

            // Se establece el id de estado 'Ocupado'
            $reservado = AsientoEstado::where('estado', 'reservado')->first();

            if (!$reservado) {
                Log::info("Error al recuperar el estado 'ocupado' del asiento.", ['datos_validados' => $this->datos_validados]);
                $this->ultimoError = "Error al actualizar los asientos. Por favor, inténtalo más tarde.";
                return false;
            }

            // Se actualizan todos los id de asientos recogidos al id ocupado
            $filas_afectadas = Asiento::whereIn('id_asiento', $asientos_id)
                                        ->update(['estado' => $reservado->id]);

            if ($filas_afectadas !== $this->asientos->count()) {
                $this->ultimoError = "No se pudieron actualizar todos los asientos. Por favor, inténtalo más tarde.";
                Log::warning($this->ultimoError, [
                    'ids_a_actualizar' => $asientos_id,
                    'filas_afectadas_db' => $filas_afectadas
                ]);
                return false; 
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error generando las entradas: " . $e->getMessage());
            $this->ultimoError = "Error al actualizar los asientos. Por favor, inténtalo más tarde.";
            return false;
        }
    }


    // Realizar pago (Se usa entorno de pruebas de Redsys)
    // Librería ssheduardo/redsys-laravel=~1.4
    private function preparar_pago_redsys(): bool {
        // Comprobar que hemos generado una factura correctamente
        if (!$this->factura || !$this->factura->num_factura) {
            $this->ultimoError = "No se ha generado una factura o número de pedido para Redsys.";
            Log::error($this->ultimoError);
            return false;
        }

        // Con el num_factura generamos el id_pedido para redsys
        $pedido_redsys_id = $this->factura->pedido_redsys_id;

        $cantidad = (float) $this->datos_validados["precio_final"];

        // Llamar al método que devolverá el formulario HTML de redsys (o error)
        $redsys_controller = new RedsysController();
        
        $redsys_respuesta = $redsys_controller->redireccionar_redsys($pedido_redsys_id, $cantidad);

        // Gestionar la respuesta
        if ($redsys_respuesta) {
            $this->redsys_form_html = $redsys_respuesta;
            return true;
        } else {
            $this->ultimoError = "No se pudo generar el formulario de pago de Redsys.";
            Log::error($this->ultimoError, ['pedido_redsys_id' => $pedido_redsys_id]);
            Log::error($redsys_respuesta);
            return false;
        }
    }
}
