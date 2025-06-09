<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PeticionPeliculasController extends Controller
{
    public static function peticion_peliculas() {
        // Recogemos los datos de las películas más populares
        // Si la petición es exitosa, se procesa la respuesta y se devuelve. Si no, se muestra un mensaje de error
        $api_key = env("API_KEY_TMDB");
        $response = Http::get("https://api.themoviedb.org/3/movie/popular?api_key={$api_key}&language=es");

        if ($response->successful()) {
            $peliculas = $response->json()['results'];
            $peliculas = PeticionPeliculasController::formatear_url($peliculas);

            return $peliculas;
        } else {
            $peliculas = ['No se pudieron recuperar peliculas'];
            return $peliculas;
        }
    }

    // Recuperamos las URL de las imágenes desde la propia API
    private static function formatear_url($peliculas) {
        $url = "https://image.tmdb.org/t/p/original/";

        foreach ($peliculas as &$pelicula) {
            if (isset($pelicula['poster_path'])) {
                $pelicula['poster_url'] = $url . $pelicula['poster_path'];
            }
            if (isset($pelicula['backdrop_path'])) {
                $pelicula['backdrop_url'] = $url . $pelicula['backdrop_path'];
            }
        }

        return $peliculas;
    }

    // Recogemos los géneros de las películas. 
    // Si la petición es exitosa, se procesa la respuesta y se devuelve. Si no, se muestra un mensaje de error
    public static function peticion_generos() {
        $api_key = env("API_KEY_TMDB");
        $response = Http::get("https://api.themoviedb.org/3/genre/movie/list?api_key={$api_key}&language=es");

        if ($response->successful()) {
            $generos = $response->json()['genres'];

            return $generos;
        } else {
            $generos = ['No se pudieron recuperar los géneros'];
            return $generos;
        }
    }


}

