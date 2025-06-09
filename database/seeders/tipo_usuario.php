<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class tipo_usuario extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tipo_usuario')->insert([
            ['tipo' => 'Administrador'],
            ['tipo' => 'Empleado'],
            ['tipo' => 'Cliente'],
        ]);
    }
}
