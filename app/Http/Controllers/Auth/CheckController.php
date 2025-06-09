<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; 
use Illuminate\Http\JsonResponse;

class CheckController extends Controller
{
    /**
     * Comprueba si un email ya existe en la tabla users.
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $email = $request->query('email'); // Obtiene el email del parámetro 'email' en la URL

        // Valida que el email no esté vacío
        if (empty($email)) {
            return response()->json(['exists' => false, 'error' => 'Email parameter missing'], 400);
        }

        // Consulta la base de datos
        $exists = User::where('email', $email)->exists();

        // Devuelve la respuesta JSON
        return response()->json(['exists' => $exists]);
    }

    /**
     * Comprueba si un DNI ya existe en la tabla users.
     */
    public function checkDni(Request $request): JsonResponse
    {
         $dni = $request->query('dni'); // Obtiene el DNI del parámetro 'dni' en la URL

         // Valida que el DNI no esté vacío
        if (empty($dni)) {
             return response()->json(['exists' => false, 'error' => 'DNI parameter missing'], 400); // Bad request
        }

         // Consulta la base de datos
        $exists = User::where('dni', $dni)->exists();

         // Devuelve la respuesta JSON
        return response()->json(['exists' => $exists]);
    }

}