<?php

namespace Database\Seeders;


use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class tipo_entrada extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tipo_entrada')->insert([
            [
                'tipo' => 'Normal',
                'precio' => 10
            ],
            [
                'tipo' => 'Espectador',
                'precio' => 5
            ],
        ]);
    }
}
