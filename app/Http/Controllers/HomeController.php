<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Ciudad;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\PeliculasController;
use App\Http\Controllers\PeliculasEstrenoController;

class HomeController extends Controller
{
    //Función index
    public function index(){
        // Se recuperan las ciudades de la BBDD
        $ciudades = Ciudad::all();
        $menus = DB::table('menus')->where('activo', true)->get();

        // Se recuperan las películas activas
        $peliculas = PeliculasController::recuperar_peliculas_activas();

        // Se recuperan las películas en estreno
        $peliculas_estreno = PeliculasEstrenoController::recuperar_peliculas_estreno();
        
        // Se devuelve la vista principal con los distintos arrays que necesitaremos
        return view('principal', compact('ciudades', 'peliculas', 'peliculas_estreno', 'menus')); 

    }

}
