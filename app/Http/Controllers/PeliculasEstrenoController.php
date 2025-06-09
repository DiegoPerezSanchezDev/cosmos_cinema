<?php

namespace App\Http\Controllers;

use App\Models\Pelicula;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PeliculasEstrenoController extends Controller
{
    // Recuperar películas y sus generos asociados
    public static function recuperar_peliculas_estreno()
    {
        $peliculas_estreno = array();

        // Query para filtrar películas en estreno
        $peliculas_objeto = Pelicula::with('generos')
            ->where('pelicula.activa', true)
            ->where('pelicula.estreno', true)
            ->get();

        foreach ($peliculas_objeto as $pelicula) {
            $peliculas_estreno[$pelicula->id] = [
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
                'estreno' => $pelicula->estreno,
                'generos' => $pelicula->generos->pluck('genero')->toArray(),
                'duracion' => $pelicula->duracion,
            ];
        }

        return $peliculas_estreno;
    }

    public static function formatear_url($ruta)
    {
        $url_api = "https://image.tmdb.org/t/p/original/";
        $url = "";

        if (isset($ruta)) {
            $url = $url_api . $ruta;
        }

        return $url;
    }
}
