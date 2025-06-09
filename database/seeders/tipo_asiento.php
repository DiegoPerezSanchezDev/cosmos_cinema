<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class tipo_asiento extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tipo_asiento')->insert([
            ['tipo' => 'Normal'],
            ['tipo' => 'VIP'],
            ['tipo' => 'Doble'],
        ]);
    }
}
