<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // Asegúrate de que la ruta a tu modelo User es correcta
use App\Models\NominaEmpleados; // O NominaEmpleados si no cambiaste el nombre
use Illuminate\Support\Facades\DB; // Si quieres desactivar foreign key checks temporalmente
use Carbon\Carbon;

class NominaEmpleadoSeeder extends Seeder
{
    public function run(): void
    {
        // Desactivar chequeo de claves foráneas si es necesario y estás truncando
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // NominaEmpleado::truncate(); // Opcional: Limpiar la tabla antes de sembrar

        // Obtener algunos empleados existentes (ajusta esto a tu lógica)
        // Por ejemplo, si los empleados tienen un tipo_usuario específico
        $empleados = User::where('tipo_usuario', 2)->take(5)->get(); // Asume tipo_usuario 2 es empleado

        if ($empleados->isEmpty()) {
            $this->command->info('No se encontraron usuarios empleados para asignar nóminas. Crea empleados primero.');
            return;
        }

        foreach ($empleados as $empleado) {
            // Crear algunas nóminas para cada empleado
            for ($i = 0; $i < 3; $i++) { // Crear 3 nóminas por empleado
                $mes = rand(1, 12);
                $anio = Carbon::now()->subYears(rand(0, 2))->year; // Nóminas de los últimos 3 años
                $fechaGeneracion = Carbon::createFromDate($anio, $mes, rand(5, 10)); // Fecha de generación a principios de mes

                $salarioBruto = rand(120000, 300000) / 100; // Entre 1200.00 y 3000.00
                $ssDeduccion = $salarioBruto * (rand(600, 700) / 10000); // Aprox 6-7%
                $irpfDeduccion = $salarioBruto * (rand(1000, 2000) / 10000); // Aprox 10-20%
                $otrasDeducciones = rand(0, 5000) / 100;
                $salarioNeto = $salarioBruto - $ssDeduccion - $irpfDeduccion - $otrasDeducciones;

                // Evitar duplicados si ya existe una nómina para ese empleado, mes y año
                if (!NominaEmpleados::where('id_empleado', $empleado->id)
                                ->where('mes', $mes)
                                ->where('anio', $anio)
                                ->exists()) {
                    NominaEmpleados::create([
                        'id_empleado' => $empleado->id,
                        'mes' => $mes,
                        'anio' => $anio,
                        'generacion_fecha' => $fechaGeneracion,
                        'salario_bruto' => $salarioBruto,
                        'deducciones_seguridad_social' => $ssDeduccion,
                        'irpf' => $irpfDeduccion,
                        'otras_deducciones' => $otrasDeducciones,
                        'salario_neto' => $salarioNeto,
                        // 'ruta_pdf' => null, // Dejar nulo por ahora
                    ]);
                }
            }
        }
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // Reactivar si lo desactivaste
        $this->command->info('Nóminas de empleados sembradas!');
    }
}