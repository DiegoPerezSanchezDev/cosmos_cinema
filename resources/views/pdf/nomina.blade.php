<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nómina {{ $nomina->periodoCompleto }} - {{ $empresa->nombre_legal ?? config('app.company_name', 'Cosmos Cinema') }}</title>
    <style>

        body {
            font-family: 'Arial', sans-serif; 
            font-size: 10pt;
            margin: 0; 
        }

        @page {
            margin: 70px 40px 60px 40px;
        }

        .header {
            width: 100%;
            text-align: center;
            position: fixed;
            left: 0;
            right: 0;
            top: -50px;
            padding-bottom: 5px;
        }

        .header h2, .header h3 {
            margin: 0;
            padding: 0;
        }
        .header h2 {
            font-size: 14pt;
            margin-bottom: 3px;
        }
        .header h3 {
            font-size: 11pt;
            font-weight: normal;
        }

        .company-info,
        .employee-details {
            width: 100%;
            margin-bottom: 20px;
            overflow: auto;
        }

        .company-info div,
        .employee-details div {
            display: inline-block;
            width: 48%;
            vertical-align: top;
            font-size: 9.5pt;
        }

        .company-info div:first-child,
        .employee-details div:first-child {
            padding-right: 2%;
        }

        .employee-details {
            margin-top: 15px;
            border-top: 1px solid #ccc;
            padding-top: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9.5pt;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #e9e9e9;
            font-weight: bold;
        }

        .text-right {
            text-align: right !important;
        }
        .text-center {
            text-align: center !important;
        }
        .font-bold {
            font-weight: bold;
        }

        .total-row td {
            font-weight: bold;
            background-color: #f0f0f0;
        }
        .grand-total-row td {
            font-weight: bold;
            background-color: #e0e0e0;
            font-size: 11pt;
            padding: 8px;
        }

        .section-title {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 8px;
            padding-bottom: 3px;
            border-bottom: 1px solid #555;
        }

        .signatures {
            margin-top: 30px;
            overflow: auto;
            font-size: 9pt;
        }

        .signatures-container {
    margin-top: 40px;
    width: 100%;
    page-break-inside: avoid;
    }

    .signatures-container .signature-block-left,
    .signatures-container .signature-block-right {
        width: 48%;
        vertical-align: top;
        text-align: center;
        padding-top: 10px;
        font-size: 9pt;
    }

    .signatures-container .signature-block-left {
        float: left;
    }

    .signatures-container .signature-block-right {
        float: right;
    }

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

    <div class="header">
        <h2>RECIBO DE SALARIOS</h2>
        <h3>{{ $empresa->nombre_legal ?? config('app.company_name', 'Cosmos Cinema') }}</h3>
    </div>

    <div class="footer">
        {{ $empresa->nombre_legal ?? config('app.company_name', 'Cosmos Cinema') }} -
        CIF: {{ $empresa->cif ?? 'B12345678' }}
        <span class="page-number-container">Página <span class="page"></span> de <span class="topage"></span></span>
    </div>

    <div class="page-content">
        <div class="company-info">
            <div>
                <strong>Empresa:</strong> {{ $empresa->nombre_legal ?? config('app.company_name', 'Cosmos Cinema') }}<br>
                <strong>CIF:</strong> {{ $empresa->cif ?? 'B12345678' }}<br>
                <strong>Domicilio:</strong> {{ $empresa->direccion ?? 'Calle Falsa 123, Ciudad, CP' }}<br>
            </div>
            <div class="text-right">
                <strong>Período Liquidación:</strong> {{ $nomina->periodoCompleto }}<br>
                <strong>Fecha Generación:</strong> {{ $nomina->generacion_fecha->format('d/m/Y') }}<br>
                <strong>Nº Recibo:</strong> {{ $nomina->id }}
            </div>
        </div>

        @php
            $categoriaProfesional = 'No especificada';
            if (isset($empleado->tipo_usuario)) {
                if ($empleado->tipo_usuario == 1) {
                    $categoriaProfesional = 'Administrador del Cine';
                } elseif ($empleado->tipo_usuario == 2) {
                    $categoriaProfesional = 'Empleado de Cine';
                }
            }
        @endphp

        <div class="employee-details">
            <div>
                <strong>Trabajador/a:</strong> {{ $empleado->nombre }} {{ $empleado->apellidos }}<br>
                <strong>DNI/NIE:</strong> {{ $empleado->dni }}<br>
                <strong>Nº Afiliación S.S.:</strong> {{ $empleado->n_seguridad_social ?? 'PENDIENTE' }}<br>
                <strong>Dirección:</strong> {{ $empleado->direccion ?? 'No especificada' }}, {{ $empleado->codigo_postal ?? '' }} {{ $empleado->city ? ($empleado->city->nombre ?? ($empleado->ciudad->nombre ?? '')) : ($empleado->ciudad->nombre ?? '') }}
            </div>
            <div class="text-right">
                <strong>Categoría Profesional:</strong> {{ $categoriaProfesional }}<br>
                <strong>Nº Teléfono:</strong> {{ $empleado->numero_telefono ?? '-' }}
            </div>
        </div>

        <div class="section-title">I. DEVENGOS</div>
        <table>
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th class="text-right">Importe</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Salario Bruto (Según Convenio/Pacto)</td>
                    <td class="text-right">{{ number_format($nomina->salario_bruto, 2, ',', '.') }} €</td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td class="font-bold">A. TOTAL DEVENGADO</td>
                    <td class="text-right font-bold">{{ number_format($nomina->salario_bruto, 2, ',', '.') }} €</td>
                </tr>
            </tfoot>
        </table>

        <div class="section-title">II. DEDUCCIONES</div>
        <table>
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th class="text-right">Importe</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Aportaciones del trabajador a la Seguridad Social</td>
                    <td class="text-right">{{ number_format($nomina->deducciones_seguridad_social, 2, ',', '.') }} €</td>
                </tr>
                <tr>
                    <td>Retención I.R.P.F.</td>
                    <td class="text-right">{{ number_format($nomina->irpf, 2, ',', '.') }} €</td>
                </tr>
                @if($nomina->otras_deducciones > 0)
                <tr>
                    <td>Otras Deducciones</td>
                    <td class="text-right">{{ number_format($nomina->otras_deducciones, 2, ',', '.') }} €</td>
                </tr>
                @endif
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td class="font-bold">B. TOTAL A DEDUCIR</td>
                    <td class="text-right font-bold">{{ number_format($nomina->deducciones_seguridad_social + $nomina->irpf + $nomina->otras_deducciones, 2, ',', '.') }} €</td>
                </tr>
            </tfoot>
        </table>

        <table>
            <tr class="grand-total-row">
                <td class="font-bold">LÍQUIDO TOTAL A PERCIBIR (A-B)</td>
                <td class="text-right font-bold">{{ number_format($nomina->salario_neto, 2, ',', '.') }} €</td>
            </tr>
        </table>

        <div class="signatures-container d-flex flex-direction-row">
            <div class="signature-block-left">
                Recibí, <br><br><br><br>
                Fdo.: {{ $empleado->nombre }} {{ $empleado->apellidos }}
            </div>
            <div class="signature-block-right">
                Sello y Firma de la Empresa <br><br><br><br>
                Fdo.: {{ $empresa->representante_legal ?? 'Representante Legal' }}
            </div>
        </div>
    </div>
</body>
</html>
</html>