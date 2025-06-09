<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Constants\Salas; // Asegúrate que el namespace y la clase sean correctos

class asiento extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Recoger la configuracion de todas las salas
        $configuracionesSalas = Salas::SALAS;

        // Obtener todas las sesiones de película
        $sesionesPelicula = DB::table('sesion_pelicula')->get();

        $asientosAInsertarGlobal = [];

        // Por cada sesión de película que exista
        foreach ($sesionesPelicula as $sesion) {
            $idSalaDeLaSesion = $sesion->id_sala;

            // Verificar si tenemos una configuración para esta sala
            if (!isset($configuracionesSalas[$idSalaDeLaSesion])) {
                continue;
            }

            // Obtener la configuración específica de la sala para esta sesión
            $configSalaActual = $configuracionesSalas[$idSalaDeLaSesion];
            $filasDefinidas = $configSalaActual['filas'];
            $columnasDefinidas = $configSalaActual['columnas'];
            $estadoDefectoParaHuecos = $configSalaActual['estado_defecto'];
            $tipoDefecto = $configSalaActual['tipo_defecto'];

            // Máximo de filas y columnas
            $max_fila = 0;
            if (!empty($filasDefinidas)) {
                $max_fila = max($filasDefinidas);
            }

            $max_columna = 0;
            if (!empty($columnasDefinidas)) {
                $max_columna = max($columnasDefinidas);
            }

            // Si no hay filas o columnas definidas para la sala, no podemos generar asientos (ni huecos)
            if ($max_fila === 0 || $max_columna === 0) {
                continue;
            }

            // Generar la matriz completa de asientos (incluyendo huecos) para esta sesión
            for ($fila = 1; $fila <= $max_fila; $fila++) {
                for ($columna = 1; $columna <= $max_columna; $columna++) {
                    // Comprobar si la combinación fila/columna actual es un asiento real o un hueco
                    if (in_array($fila, $filasDefinidas) && in_array($columna, $columnasDefinidas)) {
                        // Asiento
                        $estadoAleatorio = rand(1, 2);

                        $asientosAInsertarGlobal[] = [
                            'id_sesion_pelicula' => $sesion->id,
                            'estado' => $estadoAleatorio,
                            'columna' => $columna,
                            'fila' => $fila,
                            'id_sala' => $idSalaDeLaSesion,
                            'id_tipo_asiento' => $tipoDefecto,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    } else {
                        // Hueco
                        $asientosAInsertarGlobal[] = [
                            'id_sesion_pelicula' => $sesion->id,
                            'estado' => $estadoDefectoParaHuecos,
                            'columna' => -1,
                            'fila' => -1,
                            'id_sala' => $idSalaDeLaSesion,
                            'id_tipo_asiento' => $tipoDefecto,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }
        }

        // Insertar todos los asientos (y huecos) generados en bloques
        if (!empty($asientosAInsertarGlobal)) {
            foreach (array_chunk($asientosAInsertarGlobal, 500) as $chunk) {
                DB::table('asiento')->insert($chunk);
            }
        } 
    }
}