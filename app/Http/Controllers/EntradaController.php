<?php

namespace App\Http\Controllers;

use App\Models\Entrada;
use App\Models\Fecha;
use App\Models\Hora;
use Barryvdh\DomPDF\Facade\Pdf;

class EntradaController extends Controller
{
    public function descargarEntradaPdf($id_entrada)
    {
        // Cargar la entrada con sus relaciones necesarias
        $entrada = Entrada::with(['pelicula', 'salaEntrada', 'asiento', 'usuario', 'tipoEntrada'])
            ->findOrFail($id_entrada);

        // Datos de la empresa (puedes obtenerlos de config o de una tabla)
        $empresa = (object) [
            'nombre_legal' => config('company.name', 'Cosmos Cinema'),
            'cif' => config('company.cif', 'B12345678'),
        ];

        // Recuperar fecha
        $fecha_entrada = Fecha::find($entrada->fecha);

        // Recuperar hora
        $hora_entrada = Hora::find($entrada->hora);

        // Cargar la vista Blade para la entrada
        $pdf = Pdf::loadView('pdf.entrada_cine', compact('entrada', 'empresa', 'fecha_entrada', 'hora_entrada'));

        // Nombre del archivo para descarga
        $nombreArchivo = 'entrada_cosmos_cinema_' . $entrada->id_entrada . '_' . str_replace(' ', '_', $entrada->pelicula_titulo) . '.pdf';
        $nombreArchivo = preg_replace('/[^A-Za-z0-9_\-\.]/', '', $nombreArchivo); // Sanitizar nombre

        return $pdf->download($nombreArchivo);
    }

    public function previsualizarEntradaPdf($id_entrada)
    {
        // Cargar la entrada con sus relaciones necesarias
        // Es importante cargar todas las relaciones que usas en la vista del PDF
        $entrada = Entrada::with(['pelicula', 'salaEntrada', 'asiento', 'usuario', 'tipoEntrada'])
            ->findOrFail($id_entrada);

        // Datos de la empresa (puedes obtenerlos de config o de una tabla)
        $empresa = (object) [
            'nombre_legal' => config('company.name', 'Cosmos Cinema (Test)'),
            'cif' => config('company.cif', 'B99999999'),
            // Añade más datos de empresa si tu PDF los usa
        ];


        // Recuperar fecha
        $fecha_entrada = Fecha::find($entrada->fecha);

        // Recuperar hora
        $hora_entrada = Hora::find($entrada->hora);

        // Cargar la vista Blade para la entrada
        $pdf = Pdf::loadView('pdf.entrada_cine', compact('entrada', 'empresa', 'fecha_entrada', 'hora_entrada'));

        return $pdf->stream('vista_previa_entrada_' . $entrada->id_entrada . '.pdf');
    }



    public function generar_pdf($id_entrada)
    {
        // Cargar la entrada con sus relaciones necesarias
        // Es importante cargar todas las relaciones que usas en la vista del PDF
        $entrada = Entrada::with(['pelicula', 'salaEntrada', 'asiento', 'usuario', 'tipoEntrada'])
            ->findOrFail($id_entrada);

        // Datos de la empresa (puedes obtenerlos de config o de una tabla)
        $empresa = (object) [
            'nombre_legal' => config('company.name', 'Cosmos Cinema (Test)'),
            'cif' => config('company.cif', 'B99999999'),
            // Añade más datos de empresa si tu PDF los usa
        ];

        // O, para ver el PDF directamente en el navegador (como stream):
        $pdf = Pdf::loadView('pdf.entrada_cine', compact('entrada', 'empresa'));
        // $pdf->setPaper([0, 0, 283.46, 566.93], 'portrait'); // 100mm x 200mm

        return $pdf->stream('vista_previa_entrada_' . $entrada->id_entrada . '.pdf');
    }
}
