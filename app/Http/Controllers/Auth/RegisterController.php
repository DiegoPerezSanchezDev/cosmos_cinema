<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Mail\VerifyEmail;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    public function registrar(Request $request)
    {
        $recaptchaResponse = $request->input('g-recaptcha-response');

        if (empty($recaptchaResponse)) {
            return redirect()->route('principal')
                ->withErrors(['recaptcha_registro' => 'Por favor, completa el desafío reCAPTCHA.'], 'registro')
                ->withInput();
        }

        $verificationUrl = 'https://www.google.com/recaptcha/api/siteverify';
        $response = Http::asForm()->post($verificationUrl, [
            'secret' => env('RECAPTCHA_SECRET_KEY_REGISTRO'),
            'response' => $recaptchaResponse,
            'remoteip' => $request->ip(),
        ]);

        $recaptchaResult = $response->json();

        if (!isset($recaptchaResult['success']) || !$recaptchaResult['success']) {
            return redirect()->route('principal')
                ->withErrors(['recaptcha_registro' => 'La verificación reCAPTCHA falló. Inténtalo de nuevo.'], 'registro')
                ->withInput();
        }

        $validator = Validator::make($request->all(), [
            // Reglas de email
            'email' => 'required|string|email|max:191|unique:users,email,' . ($userIdToIgnore ?? 'NULL') . ',id', // Ignorar el email del usuario actual al actualizar
            'email_confirmation' => 'required|string|email|same:email',
            // Reglas de contraseña
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/',
                'regex:/[^a-zA-Z0-9]/',
            ],
        ], [
            // Mensajes para email
            'email.required' => 'El campo email es obligatorio.',
            'email.string' => 'El campo email debe ser una cadena de texto.',
            'email.email' => 'El email debe ser una dirección de correo válida.',
            'email.max' => 'El email no puede tener más de :max caracteres.',
            'email.unique' => 'Este email ya ha sido registrado.',

            // Mensajes para confirmación de email
            'email_confirmation.required' => 'El campo de confirmación de email es obligatorio.',
            'email_confirmation.string' => 'El campo de confirmación de email debe ser una cadena de texto.',
            'email_confirmation.email' => 'La confirmación de email debe ser una dirección de correo válida.',
            'email_confirmation.same' => 'El email y su confirmación no coinciden.',

            // Mensajes para password
            'password.required' => 'El campo contraseña es obligatorio.',
            'password.string' => 'El campo contraseña debe ser una cadena de texto.',
            'password.min' => 'La contraseña debe tener al menos :min caracteres.',
            'password.confirmed' => 'La contraseña y su confirmación no coinciden.',
            'password.regex' => 'La contraseña no cumple con los requisitos de formato (mayúscula, minúscula, número, especial).',
        ]);

        if ($validator->fails()) {
            return redirect()->route('principal')
                ->withErrors($validator, 'registro')
                ->withInput();
        }

        $user = User::create([
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'email_verification_token' => Str::uuid(), // Genera un UUID único
            'id_descuento' => 2, // O null si no hay descuento por defecto
            'tipo_usuario' => 3, // O el ID correspondiente al tipo de usuario por defecto
        ]);

        $user->save();

        Mail::to($user->email)->send(new VerifyEmail($user));

        return redirect()->route('principal')
            ->with('success', '¡Registro exitoso! Por favor, revisa tu correo electrónico para verificar tu cuenta.');
    }
}
