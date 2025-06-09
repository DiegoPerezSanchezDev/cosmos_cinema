<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cosmos Cinema</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="https://accounts.google.com/gsi/client" async defer></script>

    <!--  <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" /> -->

    <!--GSAPLaravel_8 No borrar, para entrar en GSAP. correo diegito866@gmail.com-->
    @vite([
        'resources/css/app.css', 
        'resources/js/app.js', 
        'resources/css/slider.css', 
        'resources/js/registro.js', 
        'resources/js/login.js', 
        'resources/js/entradas.js', 
        'resources/css/compraEntradas.css',
        "resources/js/adminDashboard.js",
        'resources/js/user.js',
        'resources/css/user_modal.css', 
        'resources/js/cartaCosmos.js',
        'resources/css/cartaCosmos.css',
        'resources/css/cartelera.css',
        'resources/css/detalle_pelicula.css',
        'resources/js/detalle_y_asientos.js',
        'resources/css/confirmar_seleccion.css',
        'resources/css/invitado.css',
        'resources/css/swiper-custom.css',
        'resources/js/slider-init.js',
        'resources/js/menu_hamburguesa.js',
        'resources/css/menu_hamburguesa.css',
        'resources/js/flash_mensaje.js',
        'resources/js/detalle_estreno.js',
        'resources/css/footer.css',
        'resources/css/footer_elemento.css',
        'resources/css/errors.css',
    ])

</head>

<body id="general">
<div class="container text-center my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="logo-errors">
                <a href="{{ route('principal') }}" alt="Cosmos Cinema">
                    <img src="{{ asset('images/logoCosmosCinema.webp') }}" alt="Cosmos Cinema Logo" class="cinema_logo_errors">
                </a>
            </div>
            <div class='subtitulo_404_div'>
                <h2 class='subtitulo_404 errors_titulo'>¡Oh no!</h2>
                <h2 class='subtitulo_404'>Algo salió mal.</h2>
            </div>
            <p class="lead">
                Lo sentimos, ha ocurrido un error inesperado en nuestros servidores.
                Ya hemos sido notificados y estamos trabajando para solucionarlo.
            </p>
            <p class="lead">
                Puedes intentar volver a la <a class='error_link' href="{{ route('principal') }}">página de inicio</a> o contactar con nosotros si crees que esto es un error.
            </p>
            <button class='error_btn'><a href='{{ route('principal') }}'>Volver a Cosmos Cinema</a></button>
        </div>

        @if(config('app.debug') && isset($exception))
            <div class="alert alert-danger mt-4 text-start">
                <h4>Detalles del Error (Solo para Desarrollo):</h4>
                <p><strong>Mensaje:</strong> {{ $exception->getMessage() }}</p>
                <p><strong>Archivo:</strong> {{ $exception->getFile() }} (Línea: {{ $exception->getLine() }})</p>
                <pre><code>{{ $exception->getTraceAsString() }}</code></pre>
            </div>
        @endif
    </div>
</div>
</body>

</html>