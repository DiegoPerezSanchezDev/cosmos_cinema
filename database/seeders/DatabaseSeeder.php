<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ciudades::class,
            tipo_asiento::class,
            sala::class,
            impuesto::class,
            descuento::class,
            tipo_usuario::class,
            users::class,
            administrador::class,
            NominaEmpleadoSeeder::class,
            genero_pelicula::class,
            MenuSeeder::class,
            pelicula::class,
            pelicula_genero::class,
            hora::class,
            fecha::class,
            sesion_pelicula::class,
            asiento_estado::class,
            asiento::class,
            tipo_entrada::class,
            FacturaSeeder::class,
            EntradaSeeder::class,
        ]);
    }
}
