<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
// use App\Rules\LetraDNI; // Descomenta si la usas y ajusta el namespace

class ModificarUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }
        $userId = $user->id;

        // DNI es opcional. Fecha Nacimiento, Ciudad y Mayor de 14 son obligatorios al completar.
        $isCompletingProfile = is_null($user->fecha_nacimiento) ||
                            is_null($user->ciudad_id) ||
                            !$user->mayor_edad_confirmado ||
                            !$user->acepta_terminos;

        return [
            'nombre' => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-ZÀ-ÖØ-öø-ÿ\s\.\-]*$/u'],
            'apellidos' => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-ZÀ-ÖØ-öø-ÿ\s\.\-]*$/u'],
            'numero_telefono' => ['nullable', 'string', 'digits:9', 'numeric'],
            'direccion' => ['nullable', 'string', 'max:150'],
            'codigo_postal' => ['nullable', 'string', 'digits:5', 'numeric'],
            'acepta_terminos' => [Rule::requiredIf($isCompletingProfile), 'boolean'],
            'fecha_nacimiento' => [
                Rule::requiredIf(is_null($user->fecha_nacimiento)),
                'nullable',
                'date',
                'before_or_equal:' . now()->subYears(14)->format('Y-m-d')
            ],
            'dni' => [ // DNI ahora es siempre opcional
                'nullable',
                'string',
                Rule::unique('users', 'dni')->ignore($userId),
                Rule::when($this->filled('dni'), [
                    'regex:/^\d{8}[A-Za-z]$/',
                    // new LetraDNI,
                ]),
            ],
            'ciudad_id' => [
                Rule::requiredIf($isCompletingProfile),
                'nullable',
                'integer',
                'exists:ciudades,id'
            ],
            'mayor_edad_confirmado' => [Rule::requiredIf($isCompletingProfile), 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.regex' => 'El nombre solo puede contener letras y espacios.',
            'apellidos.regex' => 'Los apellidos solo pueden contener letras y espacios.',
            'numero_telefono.digits' => 'El teléfono debe tener :digits dígitos.',
            'numero_telefono.numeric' => 'El teléfono solo debe contener números.',
            'ciudad_id.required' => 'Debes seleccionar una ciudad para completar tu perfil.',
            'ciudad_id.exists' => 'La ciudad seleccionada no es válida.',
            'codigo_postal.digits' => 'El código postal debe tener :digits dígitos.',
            'codigo_postal.numeric' => 'El código postal solo debe contener números.',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria para completar tu perfil.',
            'fecha_nacimiento.date' => 'La fecha de nacimiento no es una fecha válida.',
            'fecha_nacimiento.before_or_equal' => 'Debes tener al menos 14 años.',
            'dni.string' => 'El DNI debe ser texto.',
            'dni.regex' => 'El formato del DNI debe ser 8 números y 1 letra (si se proporciona).',
            'dni.unique' => 'Este DNI ya está en uso por otro usuario.',
            // 'dni.letra_dni' => 'La letra del DNI no es correcta (si se proporciona).',
            'mayor_edad_confirmado.required' => 'Debes confirmar que eres mayor de 14 años para completar tu perfil.',
            'mayor_edad_confirmado.accepted' => 'Debes confirmar que eres mayor de 14 años.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'acepta_terminos' => $this->boolean('acepta_terminos'),
            'mayor_edad_confirmado' => $this->input('mayor_edad_confirmado') ? true : false,
            'dni' => $this->input('dni') ? strtoupper(trim($this->input('dni'))) : null,
        ]);
    }
}