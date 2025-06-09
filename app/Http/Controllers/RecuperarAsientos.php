<?php

namespace App\Http\Controllers;

use App\Models\SesionPelicula;
use App\Models\Asiento;
use App\Models\Fecha;
use App\Models\Hora;
use App\Models\Pelicula;
use Auth;


class RecuperarAsientos extends Controller
{
    function recuperar_asientos_sesion($id_sesion) {
        // Recuperar todos los datos necesarios para generar los asientos de la sesión seleccionada
        $sesion_seleccionada = SesionPelicula::find($id_sesion);                            // Sesión
        $pelicula_seleccionada = Pelicula::with('generos')->find($sesion_seleccionada->id_pelicula);    // Película
        $fecha_seleccionada = Fecha::find($sesion_seleccionada->fecha);                     // Fecha
        $hora_seleccionada = Hora::find($sesion_seleccionada->hora);                        // Hora
        $asientos = Asiento::where('id_sesion_pelicula', $sesion_seleccionada->id)
                            ->where('id_sala', $sesion_seleccionada->id_sala)
                            ->get();  // Min y max columnas y fila
        $asientos_minymax = Asiento::where('id_sesion_pelicula', $id_sesion)
            ->selectRaw('MIN(fila) as min_fila, MAX(fila) as max_fila, MIN(columna) as min_columna, MAX(columna) as max_columna')
            ->first();

        // Generamos array de filas para crear mapa de asientos (para generar huecos)
        $asientos_filas = array();
        for ($fila = $asientos_minymax["min_fila"]; $fila <= $asientos_minymax["max_fila"]; $fila++) {
            $filas[] = $fila;
        }
        $asientos_filas = $filas;

        // Generamos array de columnas para crear mapa de asientos (para generar huecos)
        $asientos_columnas = array();
        for ($columna = $asientos_minymax["min_columna"]; $columna <= $asientos_minymax["max_columna"]; $columna++) {
            $columnas[] = $columna;
        }
        $asientos_columnas = $columnas;

        // Recuperamos si el usuario está logueado o es invitado
        if (Auth::check()) {
            $usuario = true;
        } else {
            $usuario = false;
        }

        // Generamos un array con todos los datos
        $datos_sesion = [
            'sesion' => $sesion_seleccionada,
            'pelicula' => $pelicula_seleccionada,
            'fecha' => $fecha_seleccionada,
            'hora' => $hora_seleccionada,
            'asientos' => $asientos,
            'asientos_filas' => $asientos_filas,
            'asientos_columnas' => $asientos_columnas,
            'usuario' => $usuario,
        ];

        return $datos_sesion;
    }
    
}
