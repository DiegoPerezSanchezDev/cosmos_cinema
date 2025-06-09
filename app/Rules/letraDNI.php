<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class letraDNI implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || strlen($value) !== 9 || !ctype_digit(substr($value, 0, 8)) || !ctype_alpha(substr($value, 8, 1))) {
            $fail("El formato básico del DNI no es correcto.");
            return;
        }

        // Extraer los 8 números y la letra
        $numbers = (int) substr($value, 0, 8);
        $letter = strtoupper(substr($value, 8, 1)); // Convertir a mayúscula para comparar

        // Tabla de correspondencia de letras para el DNI
        $validLetters = 'TRWAGMYFPDXBNJZSQVHLCKE';

        // Calcular la letra esperada
        $expectedLetter = $validLetters[$numbers % 23];

        // Comparar la letra proporcionada con la letra esperada
        if ($letter !== $expectedLetter) {
            // Si no coinciden, la validación falla
            $fail("La letra del DNI no se corresponde con los números proporcionados.");
        }
    }
}
