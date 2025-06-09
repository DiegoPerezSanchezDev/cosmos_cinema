<?php

namespace App\Http\Controllers;

use App\Models\Descuento;
use App\Models\Fecha;
use App\Models\SesionPelicula;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;

class RecuperarSesionPelicula extends Controller
{
    // Recuperar todas las sesiones por id_pelicula
    function recuperar_sesion_pelicula($id_pelicula) {
        // Recuperar la fecha de hoy y la de 5 días despúes de hoy
        $fecha_hoy = Carbon::today();
        $fecha_fin = $fecha_hoy->copy()->addDays(6);

        // Recuperar las sesiones de la película seleccionada en los próximos 6 días
        $sesiones = SesionPelicula::with(['fecha', 'hora'])
                    ->join('fecha', 'sesion_pelicula.fecha', '=', 'fecha.id')
                    ->join('hora', 'sesion_pelicula.hora', '=', 'hora.id')
                    ->where('fecha.fecha', '>=', $fecha_hoy->startOfDay())
                    ->where('fecha.fecha', '<=', $fecha_fin->endOfDay())
                    ->where('sesion_pelicula.id_pelicula', $id_pelicula)
                    ->select('sesion_pelicula.*')
                    ->orderBy('fecha.fecha', 'asc')
                    ->orderBy('hora.hora', 'asc')
                    ->get();

        foreach ($sesiones as &$sesion) {
            // Recuperar el día de la semana
            $dia_semana = $this->recuperar_dia_semana($sesion->fecha);

            $sesion["dia_semana"] = $dia_semana;
        }


        
        return $sesiones;
    }

    // Recuperar sesión por id_sesion
    function recuperar_sesion($id_sesion) {
        $sesion = SesionPelicula::with('fecha', 'hora', 'pelicula', 'sala')
                    ->where('id', $id_sesion)
                    ->firstOrFail();
        
        $sesion->pelicula->poster_url = PeliculasController::formatear_url($sesion->pelicula->poster_ruta);
        $sesion->pelicula->backdrop_url = PeliculasController::formatear_url($sesion->pelicula->backdrop_ruta);
        $sesion->dia_semana = $this->recuperar_dia_semana($sesion->fecha);
        if (Auth::check()) {
            $sesion->usuario = Auth::getUser();
            $sesion->descuento = Descuento::find($sesion->usuario->id_descuento);
        } else {
            $sesion->usuario = false;
        }

        return $sesion;
    }

    function recuperar_dia_semana($fecha) {
        // Cambiamos el lenguaje a español
        App::setLocale('es');

        // Se crea una instancia Fecha, y se pasa por Carbon para recuperar el día de la semana
        $fecha_values = Fecha::find($fecha);
        $fecha = Carbon::parse($fecha_values->fecha);
        $dia_semana = ucfirst($fecha->localeDayOfWeek);

        if (!isset($dia_semana)) {
            $dia_semana = "Indeterminado";
        }

        return $dia_semana;
    }
}
