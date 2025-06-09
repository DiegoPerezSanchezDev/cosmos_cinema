<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Factura;
// App\Models\Impuesto; // No es necesario importar si solo accedes via Factura
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class FacturacionController extends Controller
{
    public function index(Request $request)
    {
        $query = Factura::with(['user', 'impuesto']);

        if ($request->filled('fecha_desde')) {
            $query->whereDate('factura.created_at', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('factura.created_at', '<=', $request->fecha_hasta);
        }
        $facturas = $query->orderBy('factura.created_at', 'desc')->paginate(15);

        // Para el dashboard (opcional)
        $hoy = Carbon::today();
        $facturasHoy = Factura::with('impuesto')->whereDate('created_at', $hoy)->get();

        $totalNetoHoy = $facturasHoy->sum(fn($f) => $f->monto_neto_sin_impuesto); // Base imponible
        $totalImpuestosHoy = $facturasHoy->sum(fn($f) => $f->monto_impuesto);
        $totalBrutoHoy = $facturasHoy->sum(fn($f) => $f->monto_bruto_con_impuesto); // Total pagado por cliente

        return view('admin.facturacion.index', compact('facturas', 'totalBrutoHoy', 'totalImpuestosHoy', 'totalNetoHoy'));
    }

    //PDF Diario Detallado
    public function generarReporteDiarioPdf(Request $request)
    {
        $request->validate(['fecha' => 'required|date']);
        $fecha = Carbon::parse($request->fecha);

        $facturasDelDia = Factura::with(['user', 'impuesto'])
            ->whereDate('created_at', $fecha)
            ->orderBy('created_at', 'asc')
            ->get();

        $totalNetoDia = $facturasDelDia->sum(fn($f) => $f->monto_neto_sin_impuesto);
        $totalImpuestosDia = $facturasDelDia->sum(fn($f) => $f->monto_impuesto);
        $totalBrutoDia = $facturasDelDia->sum(fn($f) => $f->monto_bruto_con_impuesto);

        $pdf = Pdf::loadView('administrador.pdf.diario', compact('facturasDelDia', 'fecha', 'totalBrutoDia', 'totalImpuestosDia', 'totalNetoDia'));
        return $pdf->download('reporte-diario-' . $fecha->format('Y-m-d') . '.pdf');
    }

    //PDF Mensual Detallado
    public function generarReporteMensualPdf(Request $request)
    {
        $request->validate([
            'mes' => 'required|integer|min:1|max:12',
            'ano' => 'required|integer|min:2000|max:' . date('Y')
        ]);
        $mes = $request->mes;
        $ano = $request->ano;
        $fechaInicio = Carbon::create($ano, $mes, 1)->startOfMonth();
        $fechaFin = Carbon::create($ano, $mes, 1)->endOfMonth();

        $facturasDelMes = Factura::with(['user', 'impuesto'])
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->orderBy('created_at', 'asc')
            ->get();

        $totalNetoMes = $facturasDelMes->sum(fn($f) => $f->monto_neto_sin_impuesto);
        $totalImpuestosMes = $facturasDelMes->sum(fn($f) => $f->monto_impuesto);
        $totalBrutoMes = $facturasDelMes->sum(fn($f) => $f->monto_bruto_con_impuesto);

        $resumenDiario = $facturasDelMes->groupBy(function ($factura) {
            return $factura->created_at->format('Y-m-d');
        })->map(function ($facturasEnDia, $diaStr) {
            return (object) [
                'dia' => Carbon::parse($diaStr)->format('d/m/Y'),
                'total_neto_dia' => $facturasEnDia->sum(fn($f) => $f->monto_neto_sin_impuesto),
                'total_impuestos_dia' => $facturasEnDia->sum(fn($f) => $f->monto_impuesto),
                'total_bruto_dia' => $facturasEnDia->sum(fn($f) => $f->monto_bruto_con_impuesto),
                'cantidad_facturas_dia' => $facturasEnDia->count(),
            ];
        })->sortBy(function($item, $key){
            return Carbon::createFromFormat('d/m/Y', $item->dia)->timestamp;
        });

        $pdf = Pdf::loadView('administrador.pdf.mensual', compact('facturasDelMes', 'mes', 'ano', 'totalBrutoMes', 'totalImpuestosMes', 'totalNetoMes', 'resumenDiario'));
        return $pdf->download('reporte-mensual-' . $ano . '-' . str_pad($mes, 2, '0', STR_PAD_LEFT) . '.pdf');
    }

    //PDF Anual Resumido
    public function generarReporteAnualPdf(Request $request)
    {
        $request->validate(['ano' => 'required|integer|min:2000|max:' . date('Y')]);
        $ano = $request->ano;

        $facturasDelAno = Factura::with('impuesto')
            ->whereYear('created_at', $ano)
            ->get();

        $resumenMensual = $facturasDelAno->groupBy(function ($factura) {
            return $factura->created_at->format('Y-m');
        })->map(function ($facturasEnMes, $mesKey) {
            $carbonMes = Carbon::createFromFormat('Y-m', $mesKey);
            return (object) [
                'mes_numero' => $carbonMes->month,
                'mes_nombre' => $carbonMes->translatedFormat('F'),
                'total_neto_mes' => $facturasEnMes->sum(fn($f) => $f->monto_neto_sin_impuesto),
                'total_impuestos_mes' => $facturasEnMes->sum(fn($f) => $f->monto_impuesto),
                'total_bruto_mes' => $facturasEnMes->sum(fn($f) => $f->monto_bruto_con_impuesto),
                'cantidad_facturas_mes' => $facturasEnMes->count(),
            ];
        })->sortBy('mes_numero');

        $totalNetoAnual = $facturasDelAno->sum(fn($f) => $f->monto_neto_sin_impuesto);
        $totalImpuestosAnual = $facturasDelAno->sum(fn($f) => $f->monto_impuesto);
        $totalBrutoAnual = $facturasDelAno->sum(fn($f) => $f->monto_bruto_con_impuesto);

        $pdf = Pdf::loadView('administrador.pdf.anual', compact('resumenMensual', 'ano', 'totalBrutoAnual', 'totalImpuestosAnual', 'totalNetoAnual'));
        return $pdf->download('reporte-anual-' . $ano . '.pdf');
    }

    // Gráficos
    public function datosIngresosMensuales(Request $request): JsonResponse
{
    try {
        $anoActual = $request->input('ano', Carbon::now()->year);

        $facturasDelAno = Factura::with('impuesto')
            ->whereYear('created_at', $anoActual)
            ->get(); // No es necesario orderBy aquí si solo agrupamos por mes

        // Usar un array PHP plano para $datosPorMes
        $datosPorMesArray = [];
        $mesesLabels = []; // Para mantener el orden y las etiquetas correctas

        for ($m = 1; $m <= 12; $m++) {
            $carbonMes = Carbon::create($anoActual, $m, 1);
            // Verifica si intl está disponible, si no, usa un formato alternativo
            $mesLabel = extension_loaded('intl') ? $carbonMes->translatedFormat('M') : $carbonMes->format('M');
            
            $mesesLabels[] = $mesLabel; // Guardar la etiqueta del mes
            $datosPorMesArray[$mesLabel] = [
                'neto' => 0,
                'impuestos' => 0,
                'bruto' => 0,
            ];
        }

        foreach ($facturasDelAno as $factura) {
            if (!$factura->created_at instanceof Carbon) {
                $factura->created_at = Carbon::parse($factura->created_at);
            }
            // Verifica si intl está disponible
            $mesLabelFactura = extension_loaded('intl') ? $factura->created_at->translatedFormat('M') : $factura->created_at->format('M');

            if (array_key_exists($mesLabelFactura, $datosPorMesArray)) {
                $datosPorMesArray[$mesLabelFactura]['neto'] += $factura->monto_neto_sin_impuesto;
                $datosPorMesArray[$mesLabelFactura]['impuestos'] += $factura->monto_impuesto;
                $datosPorMesArray[$mesLabelFactura]['bruto'] += $factura->monto_bruto_con_impuesto;
            }
        }
        
        // Extraer los datos en el orden de $mesesLabels para asegurar el orden correcto en el gráfico
        $dataNeto = [];
        $dataImpuestos = [];
        $dataBruto = [];

        foreach ($mesesLabels as $label) {
            $dataNeto[] = $datosPorMesArray[$label]['neto'];
            $dataImpuestos[] = $datosPorMesArray[$label]['impuestos'];
            $dataBruto[] = $datosPorMesArray[$label]['bruto'];
        }

        return response()->json([
            'labels' => $mesesLabels, // Usar el array ordenado de etiquetas
            'datasets' => [
                ['label' => 'Ingresos Netos (Base)', 'data' => $dataNeto, 'backgroundColor' => 'rgba(75, 192, 192, 0.5)'],
                ['label' => 'Impuestos', 'data' => $dataImpuestos, 'backgroundColor' => 'rgba(255, 99, 132, 0.5)'],
                ['label' => 'Ingresos Brutos (Total)', 'data' => $dataBruto, 'backgroundColor' => 'rgba(54, 162, 235, 0.5)'],
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json(['error' => 'Ocurrió un error al generar los datos del gráfico.', 'debug_message' => $e->getMessage()], 500);
    }
}

    public function getResumenHoy(): JsonResponse
    {
        $hoy = Carbon::today();
        $facturasHoy = Factura::with('impuesto')->whereDate('created_at', $hoy)->get();

        $totalNetoHoy = $facturasHoy->sum(fn($f) => $f->monto_neto_sin_impuesto);
        $totalImpuestosHoy = $facturasHoy->sum(fn($f) => $f->monto_impuesto);
        $totalBrutoHoy = $facturasHoy->sum(fn($f) => $f->monto_bruto_con_impuesto);
        $numFacturasHoy = $facturasHoy->count();

        return response()->json([
            'totalBrutoHoy' => $totalBrutoHoy,
            'totalImpuestosHoy' => $totalImpuestosHoy,
            'totalNetoHoy' => $totalNetoHoy,
            'numFacturasHoy' => $numFacturasHoy,
        ]);
    }

    public function getListaFacturas(Request $request): JsonResponse
    {
        $query = Factura::with(['user', 'impuesto'])
                        ->orderBy('created_at', 'desc');

        // Aplicar filtros si vienen en la request
        if ($request->filled('fecha_desde')) {
            $query->whereDate('factura.created_at', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('factura.created_at', '<=', $request->fecha_hasta);
        }
        // Aquí podrías añadir más filtros si los necesitas (ej. por titular, por ID de factura)

        $facturasPaginator = $query->paginate(10)->withPath(url('/administrador/facturacion/lista-facturas')); // Paginamos, 10 por página o lo que prefieras

        // Los accessors se aplicarán automáticamente al serializar a JSON
        // Laravel se encarga de esto al devolver $facturas que es una instancia de LengthAwarePaginator
        return response()->json($facturasPaginator);
    }
}
