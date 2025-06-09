<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class genero_pelicula extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('genero_pelicula')->insert([
            [
                'id_genero_pelicula' => 28,
                'genero' => 'Acción'
            ],
            [
                'id_genero_pelicula' => 12,
                'genero' => 'Aventura'
            ],
            [
                'id_genero_pelicula' => 16,
                'genero' => 'Animación'
            ],
            [
                'id_genero_pelicula' => 35,
                'genero' => 'Comedia'
            ],
            [
                'id_genero_pelicula' => 80,
                'genero' => 'Crimen'
            ],
            [
                'id_genero_pelicula' => 99,
                'genero' => 'Documental'
            ],
            [
                'id_genero_pelicula' => 18,
                'genero' => 'Drama'
            ],
            [
                'id_genero_pelicula' => 10751,
                'genero' => 'Familia'
            ],
            [
                'id_genero_pelicula' => 14,
                'genero' => 'Fantasía'
            ],
            [
                'id_genero_pelicula' => 36,
                'genero' => 'Historia'
            ],
            [
                'id_genero_pelicula' => 27,
                'genero' => 'Terror'
            ],
            [
                'id_genero_pelicula' => 10402,
                'genero' => 'Música'
            ],
            [
                'id_genero_pelicula' => 9648,
                'genero' => 'Terror'
            ],
            [
                'id_genero_pelicula' => 10749,
                'genero' => 'Romance'
            ],
            [
                'id_genero_pelicula' => 878,
                'genero' => 'Ciencia Ficción'
            ],
            [
                'id_genero_pelicula' => 10770,
                'genero' => 'Película de TV'
            ],
            [
                'id_genero_pelicula' => 53,
                'genero' => 'Suspense'
            ],
            [
                'id_genero_pelicula' => 10752,
                'genero' => 'Bélica'
            ],[
                'id_genero_pelicula' => 37,
                'genero' => 'Western'
            ],
        ]);
    }
}
