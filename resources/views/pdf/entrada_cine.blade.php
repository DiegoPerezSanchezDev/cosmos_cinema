<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Entrada Cosmos Cinema - {{ $entrada->pelicula_titulo }}</title>
    <style>
        html {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            height: 81mm;
            margin: 0;
            padding: 0;
            background-color: black;
            color: white;
        }

        @page {
            margin: 7mm;
            size: 180mm 95mm;
        }

        .ticket-container-table {
            width: 100%;
            height: 81mm;
            border-collapse: collapse;
            border-spacing: 0;
        }

        .ticket-container-table td {
            padding: 0;
        }

        .ticket-main-cell {
            width: 68%;
        }

        .ticket-stub-cell {
            width: 32%;
        }

        .ticket {
            width: 100%;
            height: 100%;
            position: relative;
        }
        
        .ticket-main-cell {
            position: relative;
        }
        .ticket-main-cell::after {
            content: '';
            position: absolute;
            left: 102%;
            top: 0;
            bottom: 0;
            width: 0;
            border-left: 1px dashed #999;
            z-index: 5;
            height: 95mm;
        }


        .ticket-main {
            width: 100%;
            height: 100%;
            padding: 8px 10px;
            display: flex;
        }

        .ticket-stub {
            width: 100%;
            height: 100%;
            padding: 8px 8px;
            text-align: center;
        }

        .ticket-header {
            text-align: center; margin-bottom: 6px; padding-bottom: 4px;
            border-bottom: 0.5px solid #e8e8e8; width: 100%;
        }
        .ticket-header .cinema-logo {
            font-weight: bold; font-size: 9.5pt; color: white; letter-spacing: 0.5px;
        }
        .ticket-header h1 {
            margin: 2px 0 0 0; font-size: 10.5pt; color: white;
            font-weight: bold; text-transform: uppercase;
        }
        .movie-banner {
            text-align: center; margin-bottom: 10px; width: 100%;
        }
        .movie-banner h2 {
            margin: 0; font-size: 13pt; color: white; font-weight: bold;
            line-height: 1.25; word-break: break-word;
        }
        .movie-and-session-info-block {
            margin-bottom: 8px;
        }
        .movie-and-session-info-block::after {
            content: ""; display: table; clear: both;
        }
        .movie-poster-container {
            float: left; width: 65px; height: 95px; margin-right: 20px;
            margin-bottom: 5px; overflow: hidden; border: 2px solid #ccc;
        }
        .poster-image {
            width: 100%; height: 100%; display: block;
        }
        .poster-placeholder {
            width: 100%; height: 100%; display: table;
            font-size: 7pt; color: white;
        }
        .poster-placeholder span {
            display: table-cell; vertical-align: middle; text-align: center; padding: 3px;
        }
        .session-details-container {
            float: left; width: calc(100% - 65px - 10px); font-size: 7.5pt;
        }
        .session-details-container .detail-item {
            margin-bottom: 2.5px; line-height: 1.2;
        }
        .session-details-container .detail-item .label {
            font-weight: bold; color: white; display: inline-block; width: 70px;
            vertical-align: top;
        }
        .session-details-container .detail-item .value {
            color: white; display: inline;
        }
        hr.content-divider {
            border: none; border-top: 0.5px solid #ccc; margin-top: auto;
            margin-bottom: 6px; width: 100%; clear: both;
        }
        .price-info {
            text-align: center; font-size: 8.5pt; margin-bottom: 6px; width: 100%;
        }
        .price-info strong { font-size: 9.5pt; color: white; }
        .price-info span { display: block; font-size: 6.5pt; color: white; }
        .ticket-info-footer {
            font-size: 6pt; color: white; text-align: center; width: 100%;
            padding-top: 3px; border-top: 0.5px dotted #ddd; margin-top: auto;
            line-height: 1.2;
        }
        .ticket-info-footer p { margin: 0; }

        /* Estilos para el Talón (Stub) */
        .stub-logo-area {
            width: 100%; text-align: center; padding-top: 5px;
            height: 40%;
        }
        .stub-logo-area img { max-width: 95%; height: auto; }
        .stub-logo-area .cinema-name-stub { font-weight: bold; font-size: 10pt; color: #333; }
        .stub-qr-area {
            width: 100%; display: flex;
            padding-bottom: 5px;
            
        }

        .stub-qr-area img {
            display: block;
            width: 125px;
            height: 125px;
        }

        .qr-image-wrapper {
            background-color: #ffffff;
            padding: 5px; 
            border: 1px solid #dddddd;
            box-shadow: 0 0 3px rgba(0,0,0,0.1);
            display: inline-block;
        }

    </style>
</head>

@php
    // Se crea la url para recuperar el la imagen del póster (tamaño w342)
    $url_api = "https://image.tmdb.org/t/p/";
    $tamano_poster = "w342";
    $poster_ruta = $entrada->pelicula->poster_ruta ?? null;
    $poster_url = $poster_ruta ? $url_api . $tamano_poster . $poster_ruta : null;

    try {
        $qrCodeBase64 = \Milon\Barcode\Facades\DNS2DFacade::getBarcodePNG($entrada->codigo_qr, 'QRCODE', 5, 5);
    } catch (\Exception $e) {
        $qrCodeBase64 = null;
        Log::error('Error generando QR para PDF entrada ID ' . ($entrada->id_entrada ?? 'desconocido') . ': ' . $e->getMessage());
    }
@endphp

<body>
    <table class="ticket-container-table">
        <tr>
            <td class="ticket-main-cell">
                <div class="ticket">
                    <div class="ticket-main">
                        <header class="ticket-header">
                            <div class="cinema-logo">
                                <span>COSMOS CINEMA</span>
                            </div>
                        </header>

                        <section class="movie-banner">
                            <h2>{{ Str::limit($entrada->pelicula_titulo, 35) }}</h2>
                        </section>
                        <section class="movie-and-session-info-block">
                            <div class="movie-poster-container">
                                @if($poster_url && filter_var($poster_url, FILTER_VALIDATE_URL))
                                <img src="{{ $poster_url }}" alt="Poster {{ $entrada->pelicula_titulo }}" class="poster-image">
                                @else
                                <div class="poster-placeholder"><span>Poster no disponible</span></div>
                                @endif
                            </div>
                            <div class="session-details-container">
                                <div class="detail-item"><span class="label">SALA:</span> <span class="value">{{ $entrada->sala_nombre ?? $entrada->sala_id }}</span></div>
                                <div class="detail-item"><span class="label">FECHA:</span> <span class="value">{{ $fecha_entrada->fecha ? \Carbon\Carbon::parse($fecha_entrada->fecha)->format('d/m/Y') : 'N/A' }}</span></div>
                                <div class="detail-item"><span class="label">HORA:</span> <span class="value">{{ $hora_entrada->hora ? \Carbon\Carbon::parse($hora_entrada->hora)->format('H:i') : 'N/A' }}</span></div>
                                <div class="detail-item"><span class="label">FILA:</span> <span class="value">{{ $entrada->asiento_fila }}</span></div>
                                <div class="detail-item"><span class="label">COLUMNA:</span> <span class="value">{{ $entrada->asiento_columna }}</span></div>
                                <div class="detail-item"><span class="label">TIPO:</span> <span class="value">{{ $entrada->tipoEntrada->nombre ?? ($entrada->tipo_entrada_nombre ?? 'General') }}</span></div>
                            </div>
                        </section>
                        <hr class="content-divider">
                        <section class="price-info">
                            <strong>PRECIO: {{ number_format($entrada->precio_final ?? $entrada->precio, 2, ',', '.') }} €</strong>
                            @if(isset($entrada->descuento) && $entrada->descuento > 0 && isset($entrada->precio_total))
                            <span>(Precio Original: {{ number_format($entrada->precio_total, 2, ',', '.') }} €, Desc: {{ $entrada->descuento }}%)</span>
                            @elseif(isset($entrada->precio_base) && $entrada->precio_base > ($entrada->precio_final ?? $entrada->precio))
                                <span>(Precio Original: {{ number_format($entrada->precio_base, 2, ',', '.') }} €)</span>
                            @endif
                        </section>
                        <footer class="ticket-info-footer">
                            <p>Presenta esta entrada en el acceso. No reembolsable.</p>
                        </footer>
                    </div>
                </div>
            </td>
            <td class="ticket-stub-cell">
                <div class="ticket-stub">
                    <div class="stub-logo-area">
                        @php $ruta_logo = public_path('images/logoCosmosCinema.webp'); @endphp
                            @if(file_exists($ruta_logo))
                                <img src="{{ $ruta_logo }}" ...>
                            @else
                                <p>Logo no encontrado en: {{ $ruta_logo }}</p>
                            @endif
                    </div>
                    <div class="stub-qr-area">
                        <div class="qr-image-wrapper">
                            @if($qrCodeBase64)
                                <img src="data:image/png;base64,{{ $qrCodeBase64 }}" alt="Código QR">
                            @else
                                <p style="font-size: 7pt; color: #cc0000; background-color: white; padding: 5px;">Error QR</p>
                            @endif
                        </div>
                    </div>
                </div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>