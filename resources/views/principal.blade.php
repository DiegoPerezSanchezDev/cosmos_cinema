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
    <script>
        function onRecaptchaLoadCallback() {
            window.grecaptchaApiReady = true;
        }
    </script>
    <script src="https://www.google.com/recaptcha/api.js?onload=onRecaptchaLoadCallback&render=explicit" async defer></script>
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
    <div id='top'></div>
    <section class="cloneable">
        
        <div class="main">
            <section class="header-section">
                <!-- Header con botones -->
                <x-vistas.header/>
            </section>

            <!-- Slider horizontal de películas -->
            <section id="seccion_cartelera" class="seccion_cartelera">
                <x-vistas.mostrar_peliculas_slider :peliculas='$peliculas'/>
            </section>
            
        </div>
    </section>

    <!-- Sección de Cartelera -->
    <section id="seccion_cartelera" class="seccion_cartelera">
        <!-- Cartelera -->
        <x-vistas.mostrar_peliculas_cartelera :peliculas='$peliculas'/>
    </section>

    <!-- Modal de compra de entradas oculto inicialmente -->
    <section id="seccionCompra" class="pt-5 hidden">
        <div class='cartelera-titulo'>
            <h3>Elige tus Asientos</h3>
        </div>
        <div class='separador_compra'></div>
        <div class="seccion-compra-contenido">
            <!-- Fechas y horas de sesiones de la película -->
            <div class='seccion_sesiones' id='seccion_sesiones'>
                <div class='seccion_sesiones_dias' id='seccion_sesiones_dias'></div>
                <div class='seccion_sesiones_horas' id='seccion_sesiones_horas'></div>
            </div>
            <!-- Datos de la sesión y asientos asociados a ella -->
            <div class='seccion_asientos' id='seccion_asientos'>
                <x-vistas.seleccion_asientos/>
            </div>
        </div>
    </section>

    <!-- Sección de Estrenos -->
    <section id="seccion_estreno" class="seccion_estreno">
        <!-- Estrenos -->
        <x-vistas.mostrar_peliculas_estreno :peliculas_estreno='$peliculas_estreno'/>
    </section>

    <!-- Section para los menus -->
    <section id="seccionMenus" class="py-5">
        <x-vistas.mostrar_menu :menus='$menus'/>
    </section>

    <!-- Footer -->
    <footer>
        <x-vistas.footer/>
    </footer>

    <!-- Modal de registro nuevo usuario -->
    <x-modal.modal_registro :ciudades='$ciudades'/>

    <!-- Modal login de usuario -->
    <x-modal.modal_login/>

    <!-- Modal de ficha de usuario -->
    <x-modal.modal_usuario/>

    <!-- Modal de detalle de la película y COMPRAR ENTRADAS -->
    <x-modal.modal_detalle_pelicula/>

    <!-- Modal de Comprar como Invitado -->
    <x-modal.modal_comprar_como_invitado/>

    <!-- Modal de confirmar selección de asientos -->
    <x-modal.modal_confirmar_seleccion/>

    @stack('scripts')

</body>

</html>