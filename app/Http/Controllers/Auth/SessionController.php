<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pelicula;
use App\Models\Sesion;
use App\Models\Sala;
use App\Models\Asiento; 
use App\Models\Hora;
use App\Models\Fecha;
use App\Constants\Salas;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException; // Importa la clase ValidationException

class SessionController extends Controller
{
    /**
     * Devuelve la lista de películas activas y en cartelera para el select.
     */
    public function getPeliculasActivasEnCartelera()
    {
        $peliculas = Pelicula::where('estreno', 0)
            ->get(['id', 'titulo']);
        return response()->json($peliculas);
    }

    /**
     * Devuelve las horas disponibles para una fecha, película y sala dadas.
     */
    public function getHorasDisponibles(Request $request)
    {
        $request->validate([
            'fecha_id' => ['required', 'exists:fecha,id'],
            'pelicula_id' => ['required', 'exists:pelicula,id'],
            'sala_id' => ['required', 'exists:sala,id_sala'], // Asumo que la PK de 'sala' es 'id_sala'.
        ], [
            'fecha_id.required' => 'La fecha es obligatoria.',
            'fecha_id.exists' => 'La fecha seleccionada no es válida.',
            'pelicula_id.required' => 'La película es obligatoria.',
            'pelicula_id.exists' => 'La película seleccionada no es válida.',
            'sala_id.required' => 'La sala es obligatoria.',
            'sala_id.exists' => 'La sala seleccionada no es válida.',
        ]);

        $fechaId = $request->input('fecha_id');
        $salaId = $request->input('sala_id');
        $peliculaId = $request->input('pelicula_id');

        $peliculaSeleccionada = Pelicula::findOrFail($peliculaId);
        $duracionPelicula = $peliculaSeleccionada->duracion; // Duración en minutos.
        $margenSeguridad = 10; // Minutos de limpieza o transición.

        $fechaObj = Fecha::find($fechaId);
        if (!$fechaObj) {
            return response()->json(['error' => 'Fecha no encontrada'], 400);
        }
        $fechaSeleccionadaStr = $fechaObj->fecha; // Fecha en formato YYYY-MM-DD.

        // Obtener sesiones existentes para la fecha y sala especificadas con relaciones cargadas
        $sesionesExistentes = Sesion::with(['horaRelacion', 'pelicula'])
                                    ->where('fecha', $fechaId)
                                    ->where('id_sala', $salaId)
                                    ->orderBy('hora')
                                    ->get();

        // Pre-calcular los rangos de tiempo de las sesiones ya ocupadas
        $rangosSesionesOcupadas = [];
        foreach ($sesionesExistentes as $sesion) {
            if ($sesion->horaRelacion && $sesion->pelicula) {
                $inicioSesionExistente = Carbon::parse($fechaSeleccionadaStr . ' ' . $sesion->horaRelacion->hora);

                // Calcula la hora de fin de la sesión existente, incluyendo duración y margen.
                $finSesionExistente = (clone $inicioSesionExistente)->addMinutes($sesion->pelicula->duracion + $margenSeguridad);

                $rangosSesionesOcupadas[] = [
                    'start' => $inicioSesionExistente,
                    'end' => $finSesionExistente
                ];
            }
        }

        // Obtener todas las horas posibles del sistema
        $horasPosiblesObjetos = Hora::orderBy('hora')->get();
        $horasDisponiblesConId = [];

        // Iterar sobre cada hora posible y determinar su disponibilidad
        foreach ($horasPosiblesObjetos as $horaObj) {
            $horaPosibleStr = $horaObj->hora;

            // Calcula el rango de tiempo para la nueva sesión propuesta.
            $inicioNuevaSesion = Carbon::parse($fechaSeleccionadaStr . ' ' . $horaPosibleStr);
            $finNuevaSesion = (clone $inicioNuevaSesion)->addMinutes($duracionPelicula + $margenSeguridad);

            $isAvailable = true;

            // Comprobar solapamiento con cada sesión existente
            foreach ($rangosSesionesOcupadas as $rangoOcupado) {
                // Condición de solapamiento: (inicioNueva < finOcupada) AND (finNueva > inicioOcupada)
                if ($inicioNuevaSesion < $rangoOcupado['end'] && $finNuevaSesion > $rangoOcupado['start']) {
                    $isAvailable = false;
                    break;
                }
            }

            //No mostrar horas ya pasadas si la fecha es hoy.
            if ($fechaSeleccionadaStr == Carbon::now()->toDateString() && $inicioNuevaSesion->lt(Carbon::now())) {
                $isAvailable = false;
            }


            if ($isAvailable) {
                $horasDisponiblesConId[] = [
                    'id' => $horaObj->id,
                    'text' => substr($horaPosibleStr, 0, 5), // Formato HH:MM
                ];
            }
        }

        return response()->json($horasDisponiblesConId);
    }

    /**
     * Guarda una nueva sesión en la base de datos.
     */
    public function storeSesion(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'fecha' => ['required', 'exists:fecha,id'],
                'sala_id' => ['required', 'exists:sala,id_sala'],
                'pelicula_id' => ['required', 'exists:pelicula,id'],
                'hora' => ['required', 'exists:hora,id'],
                'activa' => ['boolean'],
            ]);

            // Comprobar si ya existe una sesión para la misma sala, fecha y hora
            $existingSession = Sesion::where('fecha', $validatedData['fecha'])
                                    ->where('id_sala', $validatedData['sala_id'])
                                    ->where('hora', $validatedData['hora'])
                                    ->first();

            if ($existingSession) {
                return response()->json(['message' => 'Ya existe una sesión programada para esta sala, fecha y hora. Por favor, selecciona otra.'], 409);
            }

            // INICIO DE LA TRANSACCIÓN DE BASE DE DATOS
            // Todas las operaciones dentro de este bloque serán atómicas.
            DB::beginTransaction();

            // Crear la sesión
            $sesion = Sesion::create([
                'fecha' => $validatedData['fecha'],
                'id_sala' => $validatedData['sala_id'],
                'id_pelicula' => $validatedData['pelicula_id'],
                'hora' => $validatedData['hora'],
                'activa' => $validatedData['activa'] ?? true,
            ]);

            // Obtener la configuración detallada de la sala desde las constantes
            $idSala = $validatedData['sala_id'];
            if (!isset(Salas::SALAS[$idSala])) {
                // Si la configuración de la sala no se encuentra, lanzamos una excepción.
                // Se hará un rollback automáticamente al salir de este catch.
                throw new \Exception("Configuración de sala (ID: {$idSala}) no encontrada en la clase 'Salas'.");
            }

            $salaConfig = Salas::SALAS[$idSala];
            $filasDefinidas = $salaConfig['filas'];
            $columnasDefinidas = $salaConfig['columnas'];
            $estadoDefecto = $salaConfig['estado_defecto'];
            $tipoDefecto = $salaConfig['tipo_defecto'];

            // Verificar si el número total de asientos calculado coincide con el de la base de datos
            $numAsientosGenerados = count($filasDefinidas) * count($columnasDefinidas);
            $salaDb = Sala::find($idSala);
            if ($salaDb && $salaDb->numero_asientos !== $numAsientosGenerados) {
                // Esto es una inconsistencia, lanzamos una excepción
                throw new \Exception("Inconsistencia: Sala ID {$idSala} tiene {$salaDb->numero_asientos} asientos en DB pero {$numAsientosGenerados} definidos en constantes. Por favor, corrige la configuración.");
            }

            // Generar los registros de Asientos para la nueva sesión
            $asientosData = [];
            foreach ($filasDefinidas as $fila) {
                foreach ($columnasDefinidas as $columna) {
                    $asientosData[] = [
                        'id_sesion_pelicula' => $sesion->id,
                        'estado' => $estadoDefecto,
                        'id_sala' => $idSala,
                        'id_tipo_asiento' => $tipoDefecto,
                        'columna' => $columna,
                        'fila' => $fila,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
            }

            // Insertar masivamente los asientos
            Asiento::insert($asientosData);

            // Si todo ha ido bien, CONFIRMAR LA TRANSACCIÓN
            DB::commit();

            return response()->json(['message' => 'Sesión y asientos creados exitosamente.', 'sesion' => $sesion], 201);

        } catch (ValidationException $e) {
            // No es necesario un DB::rollBack() aquí porque la validación ocurre antes de iniciar la transacción.
            // Pero si decides iniciar la transacción antes de la validación, sí lo necesitarías.
            return response()->json([
                'message' => 'Error de validación.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Si ocurre cualquier excepción durante la creación de la sesión o los asientos,
            // REVERTIR LA TRANSACCIÓN para asegurar la consistencia de los datos.
            DB::rollBack();
            Log::error("Error al crear la sesión y/o asientos: " . $e->getMessage(), ['exception' => $e, 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Error interno al crear la sesión y/o asientos.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtiene sesiones por fecha.
     */
    public function getSessionsByDate($fecha_id)
    {
        $fechaExiste = Fecha::where('id', $fecha_id)->exists();

        if (!$fechaExiste) {
            return response()->json(['message' => 'Fecha no encontrada.'], 404);
        }

        // Cargar las sesiones filtrando por la fecha_id con relaciones
        $sessions = Sesion::where('fecha', $fecha_id)
                            ->with(['pelicula', 'sala', 'fechaRelacion', 'horaRelacion']) // Asegúrate de 'horaSession'
                            ->get();

        $margenSeguridad = 10; // Minutos de limpieza o transición.

        // Formatear los datos para el frontend
        $formattedSessions = $sessions->map(function ($session) use ($margenSeguridad) {
            $hora_inicio_str = $session->horaRelacion->hora ?? null;
            $duracion_pelicula = $session->pelicula->duracion ?? 0;

            $hora_final_str = 'N/A';

            if ($hora_inicio_str) {
                try {
                    $hora_inicio_carbon = Carbon::parse("2000-01-01 {$hora_inicio_str}");
                    $hora_final_carbon = $hora_inicio_carbon
                                            ->addMinutes($duracion_pelicula)
                                            ->addMinutes($margenSeguridad);
                    $hora_final_str = $hora_final_carbon->format('H:i');

                } catch (\Exception $e) {
                    Log::error("Error al calcular hora final para sesión {$session->id}: " . $e->getMessage());
                    $hora_final_str = 'Error calculando';
                }
            }

            return [
                'id' => $session->id,
                'pelicula_titulo' => $session->pelicula->titulo ?? 'N/A',
                'sala_nombre' => $session->sala->nombre ?? ($session->sala->id_sala ?? 'N/A'), // Usar 'nombre' si existe, sino 'id_sala'
                'hora_inicio' => substr($hora_inicio_str, 0, 5) ?? 'N/A',
                'hora_final' => $hora_final_str,
                'fecha_sesion' => $session->fechaRelacion->fecha ?? 'N/A',
                'is_active' => $session->activa, // Asumo que la columna es 'activa'
            ];
        });

        return response()->json($formattedSessions);
    }

    /**
     * Elimina una sesión específica.
     */
    public function deleteSession($sesion_id)
    {
        // Usamos una transacción para asegurar que si falla la eliminación de asientos o sesión,
        DB::beginTransaction();

        try {
            $session = Sesion::find($sesion_id);

            // Verificar si la sesión existe
            if (!$session) {
                DB::rollBack(); // Revertir la transacción si no se encuentra la sesión
                return response()->json(['message' => 'Sesión no encontrada.'], 404);
            }

            // 1. Eliminar los asientos relacionados con esta sesión
            // Asumo que tu modelo Asiento tiene una clave foránea 'sesion_id' que referencia a la tabla 'sesiones'.
            // Si la columna se llama diferente en tu tabla 'asientos', ajústala aquí.
            $deletedSeatsCount = Asiento::where('id_sesion_pelicula', $session->id)->delete();

            //Eliminar la sesión
            $session->delete();

            // Si todo fue bien, confirmamos la transacción
            DB::commit();

            // Retornar una respuesta exitosa.
            return response()->json(['message' => 'Sesión y asientos relacionados eliminados exitosamente.'], 200);

        } catch (\Exception $e) {
            // Si ocurre algún error, revertimos la transacción
            DB::rollBack();

            // Retornar una respuesta de error al cliente
            return response()->json(['message' => 'Error al eliminar la sesión y sus asientos.', 'error' => $e->getMessage()], 500);
        }
    }

}
