<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class sesion_pelicula extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $salasIds = [1, 2, 3, 4];
        $peliculasConDuracion = [
            1 => 120, 2 => 95, 4 => 90, 6 => 98, 7 => 108, 9 => 103,
            12 => 103, 15 => 169, 18 => 120, 19 => 99, 21 => 94, 23 => 102,
        ];
        $peliculasIds = array_keys($peliculasConDuracion);

        // Mapeo preciso de id_hora a minutos
        $mapaHorasAMinutos = [
            25 => (12 * 60) + 0,  // 12:00
            26 => (12 * 60) + 30, // 12:30
            27 => (13 * 60) + 0,  // 13:00
            28 => (13 * 60) + 30, // 13:30
            29 => (14 * 60) + 0,  // 14:00
            30 => (14 * 60) + 30, // 14:30
            31 => (15 * 60) + 0,  // 15:00
            32 => (15 * 60) + 30, // 15:30
            33 => (16 * 60) + 0,  // 16:00
            34 => (16 * 60) + 30, // 16:30
            35 => (17 * 60) + 0,  // 17:00
            36 => (17 * 60) + 30, // 17:30
            37 => (18 * 60) + 0,  // 18:00
            38 => (18 * 60) + 30, // 18:30
            39 => (19 * 60) + 0,  // 19:00
            40 => (19 * 60) + 30, // 19:30
            41 => (20 * 60) + 0,  // 20:00
            42 => (20 * 60) + 30, // 20:30
            43 => (21 * 60) + 0,  // 21:00
            44 => (21 * 60) + 30, // 21:30
            45 => (22 * 60) + 0,  // 22:00
            46 => (22 * 60) + 30, // 22:30
            47 => (23 * 60) + 0,  // 23:00
        ];
        $horasIdsDisponibles = array_keys($mapaHorasAMinutos);
        sort($horasIdsDisponibles); // Asegurarse de que estén ordenadas por si acaso

        $tiempoLimpiezaMinutos = 15; // Aumentado ligeramente para más espacio
        $sesionesAInsertar = [];
        $numeroDeDiasAProgramar = 14; // Programar para 2 semanas

        // Obtener el primer ID de fecha de tu tabla 'fecha'
        $primerIdFecha = DB::table('fecha')->orderBy('id')->value('id');
        if (is_null($primerIdFecha)) {
            $this->command->error("La tabla 'fecha' está vacía o no se pudo obtener el primer ID.");
            return;
        }

        for ($offsetDia = 0; $offsetDia < $numeroDeDiasAProgramar; $offsetDia++) {
            $idFechaActual = $primerIdFecha + $offsetDia;

            if (!DB::table('fecha')->where('id', $idFechaActual)->exists()) {
                $this->command->warn("ID de fecha {$idFechaActual} no encontrado, saltando día.");
                continue;
            }

            foreach ($salasIds as $idSala) {
                $proximaHoraDisponibleEnSalaMinutos = $mapaHorasAMinutos[$horasIdsDisponibles[0]];

                // Rotar la lista de películas para cada sala y día para más variedad
                $peliculasRotadas = $peliculasIds;
                for ($r = 0; $r < ($idFechaActual + $idSala) % count($peliculasIds); $r++) {
                    array_push($peliculasRotadas, array_shift($peliculasRotadas));
                }

                // Intentar programar películas
                foreach ($peliculasRotadas as $idPelicula) {
                    $duracionPeliculaMinutos = $peliculasConDuracion[$idPelicula];
                    $duracionTotalSesionMinutos = $duracionPeliculaMinutos + $tiempoLimpiezaMinutos;

                    $idHoraInicioAsignada = null;
                    $horaInicioAsignadaMinutos = -1;

                    foreach ($horasIdsDisponibles as $idHoraPotencial) {
                        $horaPotencialMinutos = $mapaHorasAMinutos[$idHoraPotencial];
                        if ($horaPotencialMinutos >= $proximaHoraDisponibleEnSalaMinutos) {
                            // Encontramos un slot potencial
                            $horaLimiteDiaMinutos = (24 * 60) + (1 * 60);

                            if (($horaPotencialMinutos + $duracionTotalSesionMinutos) <= $horaLimiteDiaMinutos) {
                                $idHoraInicioAsignada = $idHoraPotencial;
                                $horaInicioAsignadaMinutos = $horaPotencialMinutos;
                                break; // Encontramos una hora, salimos del bucle de horas
                            }
                        }
                    }

                    if ($idHoraInicioAsignada !== null) {
                        // Se encontró un hueco y una hora para la película
                        $sesionesAInsertar[] = [
                            'id_sala' => $idSala,
                            'id_pelicula' => $idPelicula,
                            'hora' => $idHoraInicioAsignada,
                            'fecha' => $idFechaActual,
                            'activa' => 1,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                        // Actualizar la próxima hora disponible en la sala
                        $proximaHoraDisponibleEnSalaMinutos = $horaInicioAsignadaMinutos + $duracionTotalSesionMinutos;
                    }
                }
            }
        }

        // Insertar en bloques para eficiencia
        if (!empty($sesionesAInsertar)) {
            foreach (array_chunk($sesionesAInsertar, 200) as $chunk) {
                DB::table('sesion_pelicula')->insert($chunk);
            }
        }
    }
}