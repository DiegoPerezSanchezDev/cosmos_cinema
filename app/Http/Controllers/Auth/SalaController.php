<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Sala;

class SalaController extends Controller
{
    /**
     * Devuelve la lista de todas las salas.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSalas()
    {
        $salas = Sala::orderBy('numero_asientos')
                    ->get(['id_sala', 'numero_asientos']);
        return response()->json($salas->map(function($sala) {
            return [
                'id_sala' => $sala->id_sala, // El ID de la sala (será el 'value' del option)
                'text' => 'Sala ' . $sala->id_sala . ' (' . $sala->numero_asientos . ' asientos)' // El texto visible en el select
            ];
        }));
    }
}