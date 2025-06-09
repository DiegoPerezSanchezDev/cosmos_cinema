<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class asiento_estado extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('asiento_estado')->insert([
            [
                'estado' => 'disponible',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'estado' => 'ocupado',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'estado' => 'reservado',
                'created_at' => now(),
                'updated_at' => now()
            ]
            ]);
    }
}
