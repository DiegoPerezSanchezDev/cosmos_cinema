<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    public function verify(Request $request, $token)
{
    $user = User::where('email_verification_token', $token)->first();

    if (!$user) {
        return redirect()->route('principal')->with('error', 'El enlace de verificación no es válido.');
    }

    $user->email_verified_at = now();
    $user->email_verification_token = null;
    $user->save();

    Auth::login($user); // Inicia sesión al usuario después de la verificación

    return redirect()->route('principal')->with('success', '¡Tu correo electrónico ha sido verificado! Ahora puedes disfrutar de Cosmos Cinema.');
}
}