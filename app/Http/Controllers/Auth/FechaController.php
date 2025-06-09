<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Fecha;
use Carbon\Carbon;

class FechaController extends Controller
{
    /**
     * Devuelve las fechas disponibles (hoy + los próximos 4 días).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFechasDisponibles()
    {
        $today = Carbon::today();
        $endDate = Carbon::today()->addDays(6); // Hoy + 5 días = 6 días en total (hoy incluido)

        // Obtener las fechas que estén dentro del rango
        // Asegúrate de que el nombre de la tabla y la columna 'fecha' sean correctos
        $fechas = Fecha::whereBetween('fecha', [$today, $endDate])
                        ->orderBy('fecha', 'asc') // Opcional: ordenar por fecha ascendente
                        ->get(['id', 'fecha']); 

        return response()->json($fechas->map(function ($fecha) {
            return [
                'id' => $fecha->id,
                'fecha' => Carbon::parse($fecha->fecha)->format('Y-m-d'),
            ];
        }));
    }
}