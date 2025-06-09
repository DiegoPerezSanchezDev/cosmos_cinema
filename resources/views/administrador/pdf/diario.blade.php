<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte Diario - {{ $fecha->format('d/m/Y') }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ccc; padding: 5px; text-align: left; }
        th { background-color: #e9e9e9; }
        .text-right { text-align: right !important; }
        .total-summary { margin-bottom: 20px; padding: 10px; border: 1px solid #eee; background-color: #f9f9f9;}
        .total-summary p { margin: 5px 0; }
        h1, h2 { text-align: center; margin-bottom: 5px;}
        h2 { margin-bottom: 20px; font-size: 1.2em;}
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
    <h2>Día: {{ $fecha->format('d/m/Y') }}</h2>

    <div class="total-summary">
        <p><strong>Total Base Imponible (Neto):</strong> <span class="text-right">{{ number_format($totalNetoDia, 2, ',', '.') }} €</span></p>
        <p><strong>Total Impuestos:</strong> <span class="text-right">{{ number_format($totalImpuestosDia, 2, ',', '.') }} €</span></p>
        <p><strong>Total Facturado (Bruto):</strong> <span class="text-right">{{ number_format($totalBrutoDia, 2, ',', '.') }} €</span></p>
        <p><strong>Número de Facturas:</strong> {{ $facturasDelDia->count() }}</p>
    </div>

    <h3>Detalle de Facturas</h3>
    <table>
        <thead>
            <tr>
                <th>ID Fact.</th>
                <th>Num. Factura</th> <th>Hora</th>
                <th>Impuesto Aplicado</th>
                <th class="text-right">M. Neto (Base)</th>
                <th class="text-right">M. Impuesto</th>
                <th class="text-right">M. Bruto (Total)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($facturasDelDia as $factura)
                <tr>
                    <td>{{ $factura->id_factura }}</td>
                    <td>{{ $factura->num_factura }}</td> <td>{{ $factura->created_at->format('H:i:s') }}</td>
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
                    <td colspan="8" style="text-align:center;">No hay facturas para este día.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>