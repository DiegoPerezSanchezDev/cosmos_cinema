<?php

namespace App\Http\Controllers;

use App\Models\Pelicula;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PeliculasController extends Controller
{
    // Recuperar películas y sus generos asociados
    public static function recuperar_peliculas_activas()
    {
        $fecha_actual = Carbon::now()->toDateString();
        $hora_actual = Carbon::now()->toTimeString();
        $peliculas = array();

        // Query para filtrar películas:
        // - Activas
        // - Que tengan sesiones
        // - Que esas sesiones tengan su fecha después la fecha y hora actual
        $peliculas_objeto = Pelicula::with('generos')
            ->where('pelicula.estreno', false)
            ->whereHas('sesiones', function ($querySesion) use ($fecha_actual, $hora_actual) {
                $querySesion->join('fecha as tabla_fecha', 'sesion_pelicula.fecha', '=', 'tabla_fecha.id')
                    ->join('hora as tabla_hora', 'sesion_pelicula.hora', '=', 'tabla_hora.id')
                    // Condición 1: La fecha de la sesión es en el futuro
                    ->where(function ($q) use ($fecha_actual, $hora_actual) {
                        $q->where('tabla_fecha.fecha', '>', $fecha_actual)
                            // Condición 2: O la fecha de la sesión es hoy Y la hora es en el futuro
                            ->orWhere(function ($qHoy) use ($fecha_actual, $hora_actual) {
                                $qHoy->where('tabla_fecha.fecha', '=', $fecha_actual)
                                    ->where('tabla_hora.hora', '>', $hora_actual);
                            });
                    })
                ;
            })
            ->get();

        foreach ($peliculas_objeto as $pelicula) {
            $peliculas[$pelicula->id] = [
                'id' => $pelicula->id,
                'adult' => $pelicula->adult,
                'backdrop_ruta' => $pelicula->backdrop_ruta,
                'backdrop_url' => self::formatear_url($pelicula->backdrop_ruta),
                'id_api' => $pelicula->id_api,
                'lenguaje_original' => $pelicula->lenguaje_original,
                'titulo_original' => $pelicula->titulo_original,
                'sinopsis' => $pelicula->sinopsis,
                'poster_ruta' => $pelicula->poster_ruta,
                'poster_url' => self::formatear_url($pelicula->poster_ruta),
                'fecha_estreno' => $pelicula->fecha_estreno,
                'titulo' => $pelicula->titulo,
                'video' => $pelicula->video,
                'activa' => $pelicula->activa,
                'generos' => $pelicula->generos->pluck('genero')->toArray(),
                'duracion' => $pelicula->duracion,
            ];
        }

        return $peliculas;
    }

    public static function formatear_url($ruta)
    {
        $url_api = "https://image.tmdb.org/t/p/w1280/";
        $url = "";

        if (isset($ruta)) {
            $url = $url_api . $ruta;
        }

        return $url;
    }
}
