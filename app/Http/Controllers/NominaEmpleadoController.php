<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\NominaEmpleados;
use Barryvdh\DomPDF\Facade\Pdf;

class NominaEmpleadoController extends Controller
{
    /**
     * Muestra la lista de nóminas del empleado autenticado con filtros.
     */
    public function showNominas(Request $request)
    {
        $user = Auth::user();

        // Verificar si el usuario es un empleado
        if (!$user || (method_exists($user, 'isEmployee') && !$user->isEmployee())) {
            abort(403, 'Acceso no autorizado.');
        }

        // Empezar con la relación del empleado para las nóminas
        $query = $user->nominas();

        // Obtener los parámetros de filtro de la solicitud
        $filterMes = $request->input('mes');
        $filterAnio = $request->input('anio');
        $filterFechaInicio = $request->input('fecha_inicio');
        $filterFechaFin = $request->input('fecha_fin');

        // Aplicar filtro por mes si está presente
        if ($filterMes) {
            $query->where('mes', $filterMes);
        }

        // Aplicar filtro por año si está presente
        if ($filterAnio) {
            $query->where('anio', $filterAnio);
        }

        // Aplicar filtro por fecha de generación (inicio)
        if ($filterFechaInicio) {
            $query->whereDate('generacion_fecha', '>=', $filterFechaInicio);
        }

        // Aplicar filtro por fecha de generación (fin)
        if ($filterFechaFin) {
            $query->whereDate('generacion_fecha', '<=', $filterFechaFin);
        }

        // Obtener las nóminas filtradas y ordenadas
        $nominas = $query->orderBy('anio', 'desc')
                        ->orderBy('mes', 'desc')
                         ->paginate(15); // AÑADIDO: Paginación para mejor UX si hay muchas nóminas

        // Pasar las nóminas y los valores de filtro actuales a la vista
        return view('empleado.nomina', compact('nominas', 'filterMes', 'filterAnio', 'filterFechaInicio', 'filterFechaFin'));
    }

    /**
     * Genera y muestra (stream) el PDF de una nómina específica.
     *
     * @param int $idNomina
     * @return \Illuminate\Http\Response
     */
    public function generarPdfNomina($idNomina)
    {
        $nomina = NominaEmpleados::with('empleado.ciudad')->findOrFail($idNomina);
        $empleado = $nomina->empleado; // El empleado al que pertenece la nómina

        $currentUser = Auth::user();
        if ($currentUser->id !== $empleado->id){
            abort(403, 'No tienes permiso para acceder a esta nómina.');
        }

        // Datos de la empresa
        $empresa = (object) [
            'nombre_legal' => config('company.name', 'Cosmos Cinema S.L.'),
            'cif' => config('company.cif', 'B12345678'),
            'direccion' => config('company.address', 'Calle Principal 1, 28001 Madrid'),
            'representante_legal' => config('company.legal_representative', 'D. Gerente D.P/C.G')
        ];

        $pdf = Pdf::loadView('pdf.nomina', compact('nomina', 'empleado', 'empresa'));

        $periodoParaNombreArchivo = str_replace('/', '-', $nomina->periodoCompleto);
        $nombreArchivo = 'nomina-' . $periodoParaNombreArchivo . '-' . $empleado->dni . '.pdf';

        return $pdf->stream($nombreArchivo);
    }

    /**
     * Genera y descarga el PDF de una nómina específica.
     *
     * @param int $idNomina // Cambiado para recibir el ID y hacer el findOrFail aquí
     * @return \Illuminate\Http\Response
     */
    public function downloadNominaPdf($idNomina) // Cambiado el parámetro para consistencia
    {
        $nomina = NominaEmpleados::with('empleado.ciudad')->findOrFail($idNomina);
        $empleado = $nomina->empleado;

        // *** AÑADIDO: Verificación de seguridad ***
        $currentUser = Auth::user();
        if ($currentUser->id !== $empleado->id /* && !$currentUser->isAdmin() */) {
            abort(403, 'No tienes permiso para descargar esta nómina.');
        }

        // Datos de la empresa (similar a generarPdfNomina)
        $empresa = (object) [
            'nombre_legal' => config('company.name', 'Cosmos Cinema S.L.'),
            'cif' => config('company.cif', 'B12345678'),
            'direccion' => config('company.address', 'Calle Principal 1, 28001 Madrid'),
            'representante_legal' => config('company.legal_representative', 'D. Gerente D.P/C.G')
        ];

        $pdf = Pdf::loadView('pdf.nomina', compact('nomina', 'empleado', 'empresa'));

        $periodoParaNombreArchivo = str_replace('/', '-', $nomina->periodoCompleto);
        $nombreArchivo = 'nomina-' . $periodoParaNombreArchivo . '-' . $empleado->dni . '.pdf';

        return $pdf->download($nombreArchivo);
    }
}