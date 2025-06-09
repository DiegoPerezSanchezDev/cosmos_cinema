<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura de Compra</title>
    <style>
        /* Estilos CSS para la factura (en línea para mayor compatibilidad) */
        body {
            font-family: DejaVu Sans, sans-serif; /* Importante para caracteres especiales */
            font-size: 10pt;
            line-height: 1.3;
            color: #333;
            position: relative; /* Necesario para posicionar la marca de agua respecto al body */
        }

        /* Estilos de la marca de agua */
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

        .factura-container {
            width: 100%;
            max-width: 800px; /* Ajusta según necesites */
            margin: 0 auto;
        }

        .factura-header {
            text-align: left;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }

        .factura-header h1 {
            font-size: 18pt;
            margin-bottom: 5px;
        }

        .factura-header p {
            margin: 5px 0;
        }

        .cliente-info {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .cliente-info h3 {
            font-size: 12pt;
            margin-bottom: 5px;
        }

        .cliente-info p {
            margin: 2px 0;
        }

        .entradas-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .entradas-table th, .entradas-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .entradas-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .factura-resumen {
            text-align: right;
            margin-bottom: 20px;
        }

        .factura-resumen p {
            margin: 5px 0;
            font-weight: bold;
        }

        .factura-footer {
            text-align: center;
            font-size: 0.9em;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        /* Estilos específicos para alinear como en el PDF */
        .entradas-table th:nth-child(5), .entradas-table td:nth-child(5),
        .entradas-table th:nth-child(6), .entradas-table td:nth-child(6),
        .entradas-table th:nth-child(7), .entradas-table td:nth-child(7) { text-align: right; }
        .factura-resumen p { text-align: right; }
    </style>
</head>
<body>
    <div class="factura-container">
        <div class="factura-header">
            <h1>{{ config('company.name') }}</h1>
            <p>Direccion: Calle Falsa 123, Ciudad, CP</p>
            <p>NIF: B12345678</p>
            <h2>Factura de Compra</h2>
            <p>Nº Factura: {{ $factura->num_factura }}</p>
            <p>Fecha: {{ $factura->created_at }}</p>
        </div>

        @if($usuario)
        <div class="cliente-info">
            <h3>Cliente</h3>
            <p>Nombre: {{ $usuario->nombre }}</p>
            <p>Email: {{ $usuario->email }}</p>
        </div>
        @endif

        <table class="entradas-table">
            <thead>
                <tr>
                    <th>Película</th>
                    <th>Sesión</th>
                    <th>Butaca(s)</th>
                    <th>Precio Unitario</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entradas as $entrada)
                <tr>
                    <td>{{ $entrada->pelicula_titulo }}</td>
                    <td>{{ $entrada->hora }}</td>
                    <td>Fila: {{ $entrada->asiento_fila }}, Columna: {{ $entrada->asiento_columna }}</td>
                    <td>{{ number_format($entrada->precio_final, 2, ',', '.') }} €</td>
                    <td>1</td>
                    <td>{{ number_format($entrada->precio_final, 2, ',', '.') }} €</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="factura-resumen">
            <p>Base Imponible (Neto): {{ number_format($factura->monto_total / 1.21, 2, ',', '.') }} €</p>
            <p>Impuestos (21%): {{ number_format($factura->monto_total - ($factura->monto_total / 1.21), 2, ',', '.') }} €</p>
            <p>Total Facturado (Bruto): {{ number_format($factura->monto_total, 2, ',', '.') }} €</p>
        </div>

        <div class="factura-footer">
            <p>¡Gracias por tu compra!</p>
            <p>Conserva esta factura para cualquier consulta.</p>
        </div>
    </div>
</body>
</html>