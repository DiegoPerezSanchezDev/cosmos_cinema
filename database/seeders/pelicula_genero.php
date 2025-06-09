<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class pelicula_genero extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('pelicula_genero')->insert([
            // Película ID 1 (API ID: 986056 - Thunderbolts*)
            ['id_pelicula' => 1, 'id_genero_pelicula' => 28],
            ['id_pelicula' => 1, 'id_genero_pelicula' => 878],
            ['id_pelicula' => 1, 'id_genero_pelicula' => 12],

            // Película ID 2 (API ID: 1197306 - A Working Man)
            ['id_pelicula' => 2, 'id_genero_pelicula' => 28],
            ['id_pelicula' => 2, 'id_genero_pelicula' => 80],
            ['id_pelicula' => 2, 'id_genero_pelicula' => 53],

            // Película ID 4 (API ID: 950387 - Una película de Minecraft)
            ['id_pelicula' => 4, 'id_genero_pelicula' => 10751],
            ['id_pelicula' => 4, 'id_genero_pelicula' => 35],
            ['id_pelicula' => 4, 'id_genero_pelicula' => 12],
            ['id_pelicula' => 4, 'id_genero_pelicula' => 14],

            // Película ID 6 (API ID: 1233069 - Extraterritorial)
            ['id_pelicula' => 6, 'id_genero_pelicula' => 53],
            ['id_pelicula' => 6, 'id_genero_pelicula' => 28],

            // Película ID 7 (API ID: 552524 - Lilo y Stitch)
            ['id_pelicula' => 7, 'id_genero_pelicula' => 10751],
            ['id_pelicula' => 7, 'id_genero_pelicula' => 35],
            ['id_pelicula' => 7, 'id_genero_pelicula' => 878],

            // Película ID 9 (API ID: 757725 - Shadow Force)
            ['id_pelicula' => 9, 'id_genero_pelicula' => 28],
            ['id_pelicula' => 9, 'id_genero_pelicula' => 53],
            ['id_pelicula' => 9, 'id_genero_pelicula' => 18],

            // Película ID 12 (API ID: 1232546 - Until Dawn)
            ['id_pelicula' => 12, 'id_genero_pelicula' => 27],
            ['id_pelicula' => 12, 'id_genero_pelicula' => 9648],

            // Película ID 15 (API ID: 575265 - Misión: Imposible - Sentencia final)
            ['id_pelicula' => 15, 'id_genero_pelicula' => 27],
            ['id_pelicula' => 15, 'id_genero_pelicula' => 9648],

            // Película ID 18 (API ID: 1397832 - La viuda negra)
            ['id_pelicula' => 18, 'id_genero_pelicula' => 53],
            ['id_pelicula' => 18, 'id_genero_pelicula' => 9648],

            // Película ID 19 (API ID: 447273 - Blancanieves)
            ['id_pelicula' => 19, 'id_genero_pelicula' => 10751],
            ['id_pelicula' => 19, 'id_genero_pelicula' => 14],

            // Película ID 21 (API ID: 1011477 - Karate Kid: Legends)
            ['id_pelicula' => 21, 'id_genero_pelicula' => 28],
            ['id_pelicula' => 21, 'id_genero_pelicula' => 12],
            ['id_pelicula' => 21, 'id_genero_pelicula' => 18],

            // Película ID 23 (API ID: 324544 - Tierras perdidas)
            ['id_pelicula' => 23, 'id_genero_pelicula' => 28],
            ['id_pelicula' => 23, 'id_genero_pelicula' => 14],
            ['id_pelicula' => 23, 'id_genero_pelicula' => 12],
        ]);
    }
}
