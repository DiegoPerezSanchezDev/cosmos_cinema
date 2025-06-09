<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte Anual - {{ $ano }}</title>
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
    <h2>Año: {{ $ano }}</h2>

    <div class="total-summary">
        <p><strong>Total Base Imponible (Neto) del Año:</strong> <span class="text-right">{{ number_format($totalNetoAnual, 2, ',', '.') }} €</span></p>
        <p><strong>Total Impuestos del Año:</strong> <span class="text-right">{{ number_format($totalImpuestosAnual, 2, ',', '.') }} €</span></p>
        <p><strong>Total Facturado (Bruto) del Año:</strong> <span class="text-right">{{ number_format($totalBrutoAnual, 2, ',', '.') }} €</span></p>
    </div>

    <h3>Resumen Mensual del Año</h3>
    <table>
        <thead>
            <tr>
                <th>Mes</th>
                <th class="text-right">Total Neto (Base)</th>
                <th class="text-right">Total Impuestos</th>
                <th class="text-right">Total Bruto</th>
                <th class="text-right">Nº Facturas</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($resumenMensual as $mesData)
                <tr>
                    <td>{{ $mesData->mes_nombre }}</td>
                    <td class="text-right">{{ number_format($mesData->total_neto_mes, 2, ',', '.') }} €</td>
                    <td class="text-right">{{ number_format($mesData->total_impuestos_mes, 2, ',', '.') }} €</td>
                    <td class="text-right">{{ number_format($mesData->total_bruto_mes, 2, ',', '.') }} €</td>
                    <td class="text-right">{{ $mesData->cantidad_facturas_mes }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;">No hay datos de facturación para este año.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>