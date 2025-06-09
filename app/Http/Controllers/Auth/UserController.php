<?php

namespace App\Http\Controllers\Auth; // O el namespace correcto App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\ModificarUserRequest; // Asegúrate que este request incluya todos los campos necesarios
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use App\Models\User; // Importa el modelo User

class UserController extends Controller
{
    // (datosUser se queda igual, parece estar bien)
    public function datosUser(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado.'], 401);
        }

        $user->load('ciudad'); 

        $userData = [
            'id' => $user->id, // Puede ser útil para el frontend
            'nombre' => $user->nombre,
            'apellidos' => $user->apellidos,
            'email' => $user->email,
            // Formatear solo si existe la fecha
            'fecha_nacimiento' => $user->fecha_nacimiento ? Carbon::parse($user->fecha_nacimiento)->format('Y-m-d') : null, // Formato para input date
            'numero_telefono' => $user->numero_telefono,
            'dni' => $user->dni,
            'direccion' => $user->direccion, // Dejar null si es null, no 'No especificada' aquí
            'ciudad_id' => $user->ciudad_id, // Enviar el ID de la ciudad para el select
            'ciudad_nombre' => $user->ciudad->nombre ?? null, // Nombre de la ciudad para mostrar
            'codigo_postal' => $user->codigo_postal,
            'acepta_terminos' => (bool) $user->acepta_terminos, // Castear a booleano
            'mayor_edad_confirmado' => (bool) $user->mayor_edad_confirmado, // Castear a booleano
            'id_descuento' => $user->id_descuento, // Dejar null si es null
            // Podrías añadir una bandera para el frontend
            'is_profile_complete' => !$this->isProfileIncomplete($user),
        ];

        return response()->json($userData);
    }

    public function mostrarTerminos()
    {
        return view('terminos');
    }

    /**
     * Modifica/Completa los datos del perfil del usuario.
     */
    public function modificarUser(ModificarUserRequest $request): JsonResponse
    {
        $validatedData = $request->validated(); // Obtener datos validados

        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado.'], 401);
        }

        // Actualizar campos básicos
        $user->nombre = $validatedData['nombre']; // ucfirst ya no es necesario aquí si lo hace el mutator en el modelo User
        $user->apellidos = $validatedData['apellidos']; // ucfirst ya no es necesario aquí
        $user->numero_telefono = $validatedData['numero_telefono'];
        $user->direccion = $validatedData['direccion'] ?? null; // Permitir null si no se envía y es nullable
        $user->ciudad_id = $validatedData['ciudad_id']; // Asegúrate que la FK se llama ciudad_id
        $user->codigo_postal = $validatedData['codigo_postal'];

        // Campos que son cruciales para "completar perfil"
        // Estos solo se deberían actualizar si se proporcionan en el request
        // y ModificarUserRequest los valida.

        if (isset($validatedData['fecha_nacimiento'])) {
            $user->fecha_nacimiento = $validatedData['fecha_nacimiento'];
        }
        if (isset($validatedData['dni'])) {
            $user->dni = $validatedData['dni'];
        }

        // Checkboxes
        // Si el checkbox no está marcado, no se envía en el request.
        // Por eso, si existe en validatedData, es true. Si no, lo asumimos como false.
        // O mejor, el ModificarUserRequest debería tener 'boolean' para estos campos.
        if ($request->has('mayor_edad_confirmado')) { // O $validatedData['mayor_edad_confirmado'] si es boolean en el request
            $user->mayor_edad_confirmado = (bool) $validatedData['mayor_edad_confirmado'];
        } else if ($this->isProfileIncomplete($user) && !isset($validatedData['mayor_edad_confirmado'])) {
            // Si es la primera vez completando y no lo marca, podría ser un error si es 'required'
            // Esto lo debería manejar la validación en ModificarUserRequest
        }

        if ($request->has('acepta_terminos')) { // O $validatedData['mayor_edad_confirmado'] si es boolean en el request
            $user->acepta_terminos = (bool) $validatedData['acepta_terminos'];
        } else if ($this->isProfileIncomplete($user) && !isset($validatedData['acepta_terminos'])) {
            // Si es la primera vez completando y no lo marca, podría ser un error si es 'required'
            // Esto lo debería manejar la validación en ModificarUserRequest
        }


        $user->save();

        $user->load('ciudad'); // Recargar relación de ciudad

        // Comprobar si el perfil está ahora completo
        $profileNowComplete = !$this->isProfileIncomplete($user);

        $usuarioModificado = [
            'id' => $user->id,
            'nombre' => $user->nombre,
            'apellidos' => $user->apellidos,
            'email' => $user->email,
            'fecha_nacimiento' => $user->fecha_nacimiento ? Carbon::parse($user->fecha_nacimiento)->format('Y-m-d') : null,
            'numero_telefono' => $user->numero_telefono,
            'dni' => $user->dni,
            'direccion' => $user->direccion,
            'ciudad_id' => $user->ciudad_id,
            'ciudad_nombre' => $user->ciudad->nombre ?? null,
            'codigo_postal' => $user->codigo_postal,
            'mayor_edad_confirmado' => (bool) $user->mayor_edad_confirmado,
            'acepta_terminos' => (bool) $user->acepta_terminos,
            'id_descuento' => $user->id_descuento,
            'is_profile_complete' => $profileNowComplete, // Enviar el estado actualizado
        ];

        return response()->json([
            'message' => 'Perfil actualizado correctamente.',
            'user' => $usuarioModificado,
            'profile_completed' => $profileNowComplete // Para que el frontend sepa si redirigir o cerrar modal
        ]);
    }


    /**
     * Helper para verificar si el perfil está incompleto.
     * (Podría ir en un Trait o en el modelo User si se usa en muchos sitios).
     */
    protected function isProfileIncomplete(User $user = null): bool
    {
        if (!$user) {
            $user = Auth::user();
        }
        if (!$user) return true;

        // Define aquí los campos que SON OBLIGATORIOS para considerar el perfil completo
        return  is_null($user->nombre) || // Nombre y apellido deberían venir de Google
                is_null($user->apellidos) ||
                is_null($user->fecha_nacimiento) ||
                is_null($user->numero_telefono) ||
                is_null($user->dni) ||
                is_null($user->ciudad_id) ||
                is_null($user->codigo_postal) ||
                !$user->mayor_edad_confirmado; // El checkbox de >14 es crucial
    }
}