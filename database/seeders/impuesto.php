<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class impuesto extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('impuesto')->insert([
            [
                'tipo' => 'IVA',
                'cantidad' => 21.00
            ],
            [
                'tipo' => 'Reducido',
                'cantidad' => 10.00
            ],
            [
                'tipo' => 'Ninguno',
                'cantidad' => 0
            ],
        ]);
    }
}
