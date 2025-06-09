<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;
use Laravel\Socialite\Two\InvalidStateException;
use GuzzleHttp\Exception\ClientException;

class SocialAuthController extends Controller
{
    const ID_TIPO_USUARIO_CLIENTE = 3;
    const ID_TIPO_USUARIO_EMPLEADO = 2;
    const ID_TIPO_USUARIO_ADMINISTRADOR = 1;

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            // Obtener el usuario de Google.
            // Intenta primero sin stateless(). Si obtienes errores de InvalidStateException, prueba con stateless().
            $googleUser = Socialite::driver('google')->user();
            // $googleUser = Socialite::driver('google')->stateless()->user();

            // Bandera para saber si se está creando un nuevo usuario
            $isNewUserFlow = false;
            $user = null; // Inicializar $user a null

            // 1. Intentar encontrar al usuario por su google_id
            $user = User::where('google_id', $googleUser->getId())->first();

            if ($user) {
                // CASO 1: Usuario encontrado directamente por google_id
                // Actualizar campos si es necesario (avatar, email_verified_at, etc.)
                $user->update([
                    'avatar' => $user->avatar ?? $googleUser->getAvatar(),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                    'nombre' => $user->nombre ?? $this->splitName($googleUser->getName())['nombre'],
                    'apellidos' => $user->apellidos ?? $this->splitName($googleUser->getName())['apellidos'],
                ]);
                Log::info('Google Login: Usuario existente encontrado por google_id.', ['user_id' => $user->id, 'email' => $user->email]);
            } else {
                // 2. Si no se encontró por google_id, buscar por email
                $user = User::where('email', $googleUser->getEmail())->first();

                if ($user) {
                    // CASO 2: Usuario encontrado por email, pero google_id era nulo o diferente.
                    $user->update([
                        'google_id' => $googleUser->getId(),
                        'avatar' => $user->avatar ?? $googleUser->getAvatar(),
                        'email_verified_at' => $user->email_verified_at ?? now(),
                        'nombre' => $user->nombre ?? $this->splitName($googleUser->getName())['nombre'],
                        'apellidos' => $user->apellidos ?? $this->splitName($googleUser->getName())['apellidos'],
                    ]);
                    Log::info('Google Login: Usuario existente encontrado por email, google_id actualizado.', ['user_id' => $user->id, 'email' => $user->email]);
                } else {
                    // CASO 3: Usuario no encontrado ni por google_id ni por email. Se crea uno nuevo.
                    $isNewUserFlow = true;
                    $nameParts = $this->splitName($googleUser->getName());

                    $user = User::create([
                        'nombre' => $nameParts['nombre'],
                        'apellidos' => $nameParts['apellidos'],
                        'email' => $googleUser->getEmail(),
                        'google_id' => $googleUser->getId(),
                        'avatar' => $googleUser->getAvatar(),
                        'password' => null, // No se necesita contraseña para login social
                        'email_verified_at' => now(), // Asumimos que Google verifica el email
                        'tipo_usuario' => self::ID_TIPO_USUARIO_CLIENTE, // Establecer tipo_usuario a Cliente
                        'id_descuento' => 2 // Falta definir esto con tu ayuda
                    ]);
                    Log::info('Google Register: Nuevo usuario creado.', ['user_id' => $user->id, 'email' => $user->email]);
                }
            }

            // Autenticar al usuario (ya sea existente o nuevo)
            Auth::login($user, true);

            // --- Lógica de Redirección Post-Login/Creación ---
            // Verificar si el login realmente funcionó antes de proceder con la lógica de perfil
            if (!Auth::check()) {
                Log::error('Google Auth Failed: Auth::check() returned false after login attempt.', ['email' => $googleUser->getEmail()]);
                return redirect()->route('principal')->withErrors(['auth' => 'Hubo un problema al intentar iniciar tu sesión. Por favor, inténtalo de nuevo.']);
            }
    
            $authenticatedUser = Auth::user();
    
            // Determinar si el perfil está incompleto
            $profileIsIncomplete = method_exists($authenticatedUser, 'isProfileIncomplete') && $authenticatedUser->isProfileIncomplete();
    
            if ($profileIsIncomplete) {
                $message = '¡Bienvenido/a! Accede a "Mi Cuenta" para completar tu perfil.';
                return redirect()->intended(route('principal'))->with('warning', $message); // Usamos 'warning' para diferenciar
            }
    
            // Si el perfil no está incompleto, mensaje de bienvenida normal
            $welcomeMessage = "¡Bienvenido ";
            if ($authenticatedUser->tipo_usuario === self::ID_TIPO_USUARIO_CLIENTE) {
                $welcomeMessage .= "Cliente {$authenticatedUser->nombre} {$authenticatedUser->apellidos}!";
            } elseif ($authenticatedUser->tipo_usuario === self::ID_TIPO_USUARIO_EMPLEADO) {
                $welcomeMessage .= "Empleado {$authenticatedUser->nombre} {$authenticatedUser->apellidos}!";
            } elseif ($authenticatedUser->tipo_usuario === self::ID_TIPO_USUARIO_ADMINISTRADOR) {
                $welcomeMessage .= "Administrador {$authenticatedUser->nombre} {$authenticatedUser->apellidos}!";
            } else {
                $welcomeMessage .= "{$authenticatedUser->nombre} {$authenticatedUser->apellidos}!";
            }
            return redirect()->intended(route('principal'))->with('success', $welcomeMessage);

        } catch (InvalidStateException $e) {
            Log::error('Google Auth Error: InvalidStateException. ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('principal')->withErrors(['auth' => 'Hubo un problema de estado con Google. Intenta de nuevo. Si persiste, borra cookies.']);
        } catch (ClientException $e) {
            $errorBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'Sin cuerpo de respuesta.';
            Log::error('Google Auth Error: ClientException. ' . $e->getMessage() . ' Response: ' . $errorBody, ['trace' => $e->getTraceAsString()]);
            return redirect()->route('principal')->withErrors(['auth' => 'Error de comunicación con Google. Verifica tu configuración.']);
        } catch (Exception $e) {
            Log::error('Google Auth Error: General Exception. ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('principal')->withErrors(['auth' => 'No se pudo autenticar con Google. Inténtalo de nuevo.']);
        }
    }

    protected function splitName($fullName)
    {
        if (empty($fullName)) {
            return ['nombre' => null, 'apellidos' => null];
        }
        $parts = explode(' ', trim($fullName), 2);
        return [
            'nombre' => $parts[0],
            'apellidos' => $parts[1] ?? null,
        ];
    }

    protected function isProfileIncomplete(User $user): bool
    {
        if (method_exists($user, 'checkIfProfileIsActuallyIncomplete')) {
            return $user->checkIfProfileIsActuallyIncomplete();
        }
        return is_null($user->nombre) ||
            is_null($user->apellidos) ||
            is_null($user->fecha_nacimiento) ||
            is_null($user->numero_telefono) ||
            is_null($user->dni) ||
            is_null($user->ciudad_id) ||
            is_null($user->codigo_postal) ||
            !$user->mayor_edad_confirmado;
    }
}