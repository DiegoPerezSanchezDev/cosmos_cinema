<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte Mensual - {{ \Carbon\Carbon::create($ano, $mes)->translatedFormat('F Y') }}</title>
    <style> /* (Mismos estilos que el diario) */
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ccc; padding: 5px; text-align: left; }
        th { background-color: #e9e9e9; }
        .text-right { text-align: right !important; }
        .total-summary { margin-bottom: 20px; padding: 10px; border: 1px solid #eee; background-color: #f9f9f9;}
        .total-summary p { margin: 5px 0; }
        h1, h2, h3 { text-align: center; margin-bottom: 5px;}
        h2 { margin-bottom: 10px; font-size: 1.2em;}
        h3 { margin-bottom: 10px; font-size: 1.1em; text-align:left; margin-top: 20px;}
        body::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url('{{ public_path('images/logoCosmosCinema.webp') }}');
    background-repeat: no-repeat;
    background-position: center center;
    background-size: 85%;  /* Ajusta este valor */
    opacity: 0.5;      /* Ajusta este valor */
    z-index: -1;
    transform: rotate(-45deg); /* Opcional, ajusta el ángulo */
}
    </style>
</head>
<body>
    <h1>Informe de Facturación</h1>
    <h2>Mes: {{ \Carbon\Carbon::create($ano, $mes)->translatedFormat('F Y') }}</h2>

    <div class="total-summary">
        <p><strong>Total Base Imponible (Neto) del Mes:</strong> <span class="text-right">{{ number_format($totalNetoMes, 2, ',', '.') }} €</span></p>
        <p><strong>Total Impuestos del Mes:</strong> <span class="text-right">{{ number_format($totalImpuestosMes, 2, ',', '.') }} €</span></p>
        <p><strong>Total Facturado (Bruto) del Mes:</strong> <span class="text-right">{{ number_format($totalBrutoMes, 2, ',', '.') }} €</span></p>
        <p><strong>Número de Facturas (Mes):</strong> {{ $facturasDelMes->count() }}</p>
    </div>

    @if($resumenDiario->count() > 0)
    <h3>Resumen Diario del Mes</h3>
    <table>
        <thead>
            <tr>
                <th>Día</th>
                <th class="text-right">Total Neto (Base)</th>
                <th class="text-right">Total Impuestos</th>
                <th class="text-right">Total Bruto</th>
                <th class="text-right">Nº Facturas</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($resumenDiario as $diaData)
                <tr>
                    <td>{{ $diaData->dia }}</td>
                    <td class="text-right">{{ number_format($diaData->total_neto_dia, 2, ',', '.') }} €</td>
                    <td class="text-right">{{ number_format($diaData->total_impuestos_dia, 2, ',', '.') }} €</td>
                    <td class="text-right">{{ number_format($diaData->total_bruto_dia, 2, ',', '.') }} €</td>
                    <td class="text-right">{{ $diaData->cantidad_facturas_dia }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <h3>Detalle de Facturas del Mes</h3>
    <table>
        <thead>
            <tr>
                <th>ID Fact.</th>
                <th>Fecha</th>
                <th>Impuesto Aplicado</th>
                <th class="text-right">M. Neto (Base)</th>
                <th class="text-right">M. Impuesto</th>
                <th class="text-right">M. Bruto (Total)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($facturasDelMes as $factura)
                <tr>
                    <td>{{ $factura->id_factura }}</td>
                    <td>{{ $factura->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        @if($factura->impuesto)
                            {{ $factura->impuesto->tipo }} ({{ $factura->impuesto->cantidad}}%)
                        @else
                            N/A
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($factura->monto_neto_sin_impuesto, 2, ',', '.') }} €</td>
                    <td>
                        @if($factura->impuesto)
                            {{ $factura->impuesto->tipo }} ({{ number_format($factura->impuesto->cantidad, 2, ',', '.') }}%)
                        @else
                            N/A
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($factura->monto_bruto_con_impuesto, 2, ',', '.') }} €</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;">No hay facturas para este mes.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>