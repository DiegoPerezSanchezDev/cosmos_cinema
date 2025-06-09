<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ProcesarPago extends Controller
{

    private ?array $datos_validados = null;

    public function procesar_pago(Request $request) {

        // Unir todas las reglas
        $todas_las_reglas = array_merge(
            $this->obtener_reglas_sesion(),
            $this->obtener_reglas_asientos()
        );

        // Unir todos los mensajes
        $todos_los_mensajes = array_merge(
            $this->obtener_mensajes_sesion(),
            $this->obtener_mensajes_asientos()
        );

        // Si el usuario está logueado, se recuperan su id, y se añaden sus reglas y mensajes
        if (Auth::check()) {
            $request->merge(['usuario_id' => Auth::id()]);

            $todas_las_reglas = array_merge($todas_las_reglas, $this->obtener_reglas_usuario());
            $todos_los_mensajes = array_merge($todos_los_mensajes, $this->obtener_mensajes_usuario($request));
        } else {    // Si es invitado
            $todas_las_reglas = array_merge($todas_las_reglas, $this->obtener_reglas_invitado());
            $todos_los_mensajes = array_merge($todos_los_mensajes, $this->obtener_mensajes_invitado());
        }

        // Se usa Validator para comprobar mediante reglas establecidas si los valores son correctos
        // Validar las reglas establecidas con los valores introducidos. Recuperar mensajes de error correspondientes
        $validator = Validator::make($request->all(), $todas_las_reglas, $todos_los_mensajes);

        // Si hay errores, se devuelven los errores y se mantienen los valores introducidos
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator, 'procesar_pago')   // Errores
                ->withInput();                              // Devolver input ya escrito
        }
        
        // Si la validación es correcta, se cambia el estado de la validación
        $this->datos_validados = $validator->validated();

        // Guardamos usuario_id en $this->datos_validados (o null si es invitado)
        if (Auth::check()) {
            $this->datos_validados['usuario_id'] = Auth::id();
        } else {
            $this->datos_validados['usuario_id'] = null;
        }

        // Comprobar que el precio total es correcto
        // Recuperar los datos del precio
        $precio_total_calculado = $this->datos_validados['precio_total'];
        $porcentaje_descuento = $this->datos_validados['precio_descuento'];
        $precio_final_esperado = $precio_total_calculado * (1 - ($porcentaje_descuento / 100));

        // Comparar con una pequeña tolerancia para evitar problemas con puntos flotantes
        $tolerancia = 0.01; // 1 céntimo de tolerancia

        if (abs($precio_final_esperado - $this->datos_validados['precio_final']) > $tolerancia) {
            // Log para indicar un posible fallo en el sistema de precios y descuentos
            Log::warning('Discrepancia en el cálculo del precio final.', [
                'precio_total' => $precio_total_calculado,
                'porcentaje_descuento' => $porcentaje_descuento,
                'precio_final_enviado' => $this->datos_validados['precio_final'],
                'precio_final_calculado_servidor' => $precio_final_esperado
            ]);

            return redirect()->back()
                ->withErrors(['calculo_precio' => 'El cálculo del precio final con el descuento aplicado no es correcto.'], 'procesar_pago')
                ->withInput();
        }

        // Se intentan generar Entradas y Factura, se actualizan los asientos a 'Reservado'
        // Se devuelve una respuesta y se maneja
        $generar_entrada_instancia = new GenerarEntrada();
        $generar_entrada_respuesta = $generar_entrada_instancia->generar_entrada($this->datos_validados);

        if ($generar_entrada_respuesta["status"] === "error") {
            $mensajeError = $generar_entrada_respuesta["message"] ?: 
                'Hubo un problema procesando tu pedido. Por favor, inténtalo de nuevo o contacta con soporte.';
            
            return redirect()->back()
                ->withErrors(['generar_entrada_custom_error' => $mensajeError], 'procesar_pago')
                ->withInput();
        }

        return $generar_entrada_respuesta['data'];
    }


    // --- Métodos para definir reglas por sección ---
    private function obtener_reglas_sesion(): array
    {
        return [
            'sesion_id'         => 'required|integer|exists:sesion_pelicula,id',
            'precio_total'      => 'required|numeric|min:1',
            'precio_descuento'  => 'required|numeric|min:0',
            'precio_final'      => 'required|numeric|min:1',
        ];
    }

    private function obtener_reglas_asientos(): array
    {
        return [
            'asiento'           => 'required|array|min:1',
            'asiento.*'         => 'required|integer|distinct|exists:asiento,id_asiento',
        ];
    }

    private function obtener_reglas_usuario(): array {
        return [
            'usuario_id' => 'required|integer|exists:users,id',
        ];
    }

    private function obtener_reglas_invitado(): array {
        return [
            'email_invitado' => 'required|email|max:255',
        ];
    }

    // --- Métodos para definir mensajes por sección ---
    private function obtener_mensajes_sesion(): array
    {
        return [
            'sesion_id.required'       => 'La información de la sesión es obligatoria.',
            'sesion_id.integer'        => 'La información de la sesión no es válida.',
            'sesion_id.exists'         => 'La sesión especificada no existe.',
            'precio_total.required'    => 'El precio total es obligatorio.',
            'precio_total.numeric'     => 'El precio total debe ser un número.',
            'precio_total.min'         => 'El precio total no puede ser negativo.',
            'precio_descuento.required'=> 'El precio con descuento es obligatorio.',
            'precio_descuento.numeric' => 'El precio con descuento debe ser un número.',
            'precio_descuento.min'     => 'El precio con descuento no puede ser negativo.',
            'precio_final.required'    => 'El precio final es obligatorio.',
            'precio_final.numeric'     => 'El precio final debe ser un número.',
            'precio_final.min'         => 'El precio final no puede ser negativo.',
        ];
    }

    private function obtener_mensajes_asientos(): array
    {
        return [
            'asiento.required'         => 'No se han seleccionado asientos.',
            'asiento.array'            => 'La selección de asientos no es válida.',
            'asiento.min'              => 'Debes seleccionar al menos un asiento.',
            'asiento.*.required'       => 'Uno de los asientos seleccionados no es válido.',
            'asiento.*.integer'        => 'Uno de los asientos seleccionados debe ser un número.',
            'asiento.*.distinct'       => 'No puedes seleccionar el mismo asiento varias veces.',
            'asiento.*.exists'         => 'Uno de los asientos seleccionados no existe o no está disponible.',
        ];
    }

    private function obtener_mensajes_usuario($request): array
    {
        return [
            'usuario_id.required'   => 'Error interno: La información del usuario autenticado es necesaria.',
            'usuario_id.integer'    => 'Error interno: La información del usuario autenticado no es válida.',
            'usuario_id.exists'     => 'Error interno: El usuario autenticado no existe en el sistema.',
        ];
    }

    private function obtener_mensajes_invitado(): array {
        return [
            'email_invitado.required' => 'Por favor, introduce tu dirección de correo electrónico para continuar como invitado.',
            'email_invitado.email'    => 'La dirección de correo electrónico introducida no es válida.',
            'email_invitado.max'      => 'La dirección de correo electrónico es demasiado larga.',
        ];
    }
}
