<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class descuento extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('descuento')->insert([
            ['descuento' => 15, 'tipo' => 'empleado'],
            ['descuento' => 10, 'tipo' => 'usuario'],
            ['descuento' => 30, 'tipo' => 'familia_numerosa'],
            ['descuento' => 80, 'tipo' => 'jubilado'],
            ['descuento' => 40, 'tipo' => 'discapacitado'],
        ]);
    }
}
