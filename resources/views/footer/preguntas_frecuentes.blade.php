<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cosmos Cinema</title>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="https://accounts.google.com/gsi/client" async defer></script>

    @vite([
        'resources/css/app.css', 
        'resources/js/app.js', 
        'resources/css/slider.css', 
        'resources/js/registro.js', 
        'resources/js/login.js', 
        'resources/js/user.js',
        'resources/css/user_modal.css', 
        'resources/js/menu_hamburguesa.js',
        'resources/css/menu_hamburguesa.css',
        'resources/css/footer.css',
        'resources/css/footer_elemento.css',
    ])

</head>

<body id="general">
    <div id='top'></div>
    <section class="cloneable cloneable_footer">
        
        <div class="main">
            <section class="header-section">
                <!-- Header con botones -->
                <x-vistas.header/>
            </section>
        </div>
    </section>

    <section class='footer-section'>
        <x-footer.preguntas_frecuentes/>
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

    @stack('scripts')

</body>

</html>