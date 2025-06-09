<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class LoginController extends Controller
{
    const ID_TIPO_USUARIO_CLIENTE = 3;
    const ID_TIPO_USUARIO_EMPLEADO = 2;
    const ID_TIPO_USUARIO_ADMINISTRADOR = 1;

    public function login(Request $request)
    {
        $mensajes = [
            'login_email.required' => 'Por favor, introduce tu dirección de email.',
            'login_email.string' => 'El email debe ser una cadena de texto.',
            'login_email.email' => 'Por favor, introduce una dirección de email válida.',
            'login_password.required' => 'Por favor, introduce tu contraseña.',
            'login_password.string' => 'La contraseña debe ser una cadena de texto.',
        ];

        $credentials = $request->validate([
            'login_email' => ['required', 'string', 'email'],
            'login_password' => ['required', 'string']
        ], $mensajes);

        $recaptchaResponse = $request->input('g-recaptcha-response');
        $recaptchaSecret = env('RECAPTCHA_SECRET_KEY_LOGIN');

        if (empty($recaptchaResponse)) {
            return back()->withErrors([
                'recaptcha_login' => 'Por favor, completa el desafío reCAPTCHA.',
            ])->onlyInput('login_email');
        }

        $verificationUrl = 'https://www.google.com/recaptcha/api/siteverify';
        $response = Http::asForm()->post($verificationUrl, [
            'secret' => $recaptchaSecret,
            'response' => $recaptchaResponse,
            'remoteip' => $request->ip(),
        ]);

        $recaptchaResult = $response->json();

        if (!isset($recaptchaResult['success']) || !$recaptchaResult['success']) {
            return back()->withErrors([
                'recaptcha_login' => 'La verificación reCAPTCHA falló. Inténtalo de nuevo.',
            ])->onlyInput('login_email');
        }

        $authCredentials = [
            'email' => $credentials['login_email'],
            'password' => $credentials['login_password'],
        ];

        if (Auth::attempt($authCredentials, $request->has('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->email_verified_at === null) {
                Auth::logout();
                return back()->with('success', 'Tu cuenta aún no ha sido verificada. Por favor, revisa tu correo electrónico.');
            }

            if (!$user->hasVerifiedEmail()) {
                Auth::logout();
                return back()->with('success', 'Tu cuenta aún no ha sido verificada. Por favor, revisa tu correo electrónico.');
            }

            if ($user->tipo_usuario === self::ID_TIPO_USUARIO_CLIENTE) {
                $request->session()->regenerate();
                return redirect()->intended('/')->with('success', "¡Bienvenido Cliente {$user->nombre} {$user->apellidos}!");
            } elseif ($user->tipo_usuario === self::ID_TIPO_USUARIO_EMPLEADO) {
                $request->session()->regenerate();
                return redirect()->intended('/')->with('success', "¡Bienvenido Empleado {$user->nombre} {$user->apellidos}!");
            } elseif ($user->tipo_usuario === self::ID_TIPO_USUARIO_ADMINISTRADOR) {
                $request->session()->regenerate();
                return redirect()->intended('/')->with('success', "¡Bienvenido Administrador {$user->nombre} {$user->apellidos}!");
            }
        } else {
            return back()->withErrors([
                'login_password' => 'Las credenciales proporcionadas no coinciden con nuestros registros.'
            ])->onlyInput('login_email');
        }
    }

    public function showLoginForm()
    {
        return view('components.login');
    }

    //Método para cerrar sesión
    public function logout(Request $request)
    {
        Auth::logout();

        // Invalida la sesión actual y regenera el token CSRF
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', '¡Sesión cerrada correctamente!');
    }
}
