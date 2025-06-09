<?php

namespace App\Http\Controllers;

use App\Models\Ciudad;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CiudadController extends Controller
{
    public function mostrar_ciudades() {
        $ciudades = Ciudad::all();

        return view('principal', ['ciudades' => $ciudades]);
    }

    public function pasar_ciudades(): JsonResponse // Indica que devuelve JsonResponse
    {
        $ciudades = Ciudad::select('id', 'nombre')->get();

        return response()->json($ciudades);
    }
}
