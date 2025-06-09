<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class fecha extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crea fechas desde hoy hasta 365 días en el futuro
        $fechaInicio = Carbon::now();
        $fechaFin = Carbon::now()->addDays(365);

        $fechas = [];
        for ($date = $fechaInicio->copy(); $date->lte($fechaFin); $date->addDay()) {
            $fechas[] = [
                'fecha' => $date->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('fecha')->insert($fechas);
    }
}
