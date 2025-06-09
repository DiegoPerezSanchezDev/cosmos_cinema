<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\CheckController;
use App\Http\Controllers\Auth\AdminController;
use App\Http\Controllers\CiudadController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\SalaController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\Auth\FechaController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\RecuperarAsientos;
use App\Http\Controllers\NominaEmpleadoController;
use App\Http\Controllers\ProcesarPago;
use App\Http\Controllers\EntradaController;
use App\Http\Controllers\FacturacionController;
use App\Http\Controllers\FooterController;
use App\Http\Controllers\RecuperarSesionPelicula;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\RedsysController;


//Ruta por get, al poner / en el buscador, nos saldra la pantalla de principal, que es devuelta por la clase HomeController y llama a la función index.
Route::get('/', [HomeController::class, 'index'])->name('principal');

//Registro
Route::post('/register', [RegisterController::class, 'registrar'])->name('registro');

//Verificar Email
Route::get('/verify-email/{token}', [VerificationController::class, 'verify'])->name('verification.verify');

Route::get('/verify-email/notice', function () {
    return view('verification.notice');
})->name('verification.notice');

// Ruta para comprobar si el email existe
Route::get('/check-email', [CheckController::class, 'checkEmail']);

// Ruta para comprobar si el DNI existe
Route::get('/check-dni', [CheckController::class, 'checkDni']);

Route::get('/login', [AdminController::class, 'mostrarLogin']);

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');

Route::post('/login', [LoginController::class, 'login'])->name('login');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

//Ruta para los datos del usuario
Route::get('/perfil/datos', [UserController::class, 'datosUser'])->name('user.datosUser')->middleware('auth');

//Ruta para modificar los datos del usuario en el modal de Mi Cuenta
Route::patch('/perfil/modificar', [UserController::class, 'modificarUser'])->name('name.modificarUser')->middleware('auth');

//Ruta para devolver las ciudades
Route::get('/ciudades', [CiudadController::class, 'pasar_ciudades'])->name('ciudades.pasar_ciudades');

//Ruta para ir al login de administradores
Route::get('/administrador',[AdminController::class, 'mostrarLogin'])->name('administrador.loginAdministrador');

//Ruta para el dashboard
Route::get('/administrador/dashboard', [AdminController::class, 'index'])
    ->name('administrador.dashboard')
    ->middleware('auth:admin');

//Login
Route::post('/administrador', [AdminController::class, 'login'])->name('admin.login.submit');

//Logout
Route::post('/administrador/logout', [AdminController::class, 'logout'])->name('administrador.logout');

//Ruta para buscar películas en la API
Route::get('/administrador/buscar-peliculas-api', [AdminController::class, 'searchTMDb'])->name('admin.searchTMDb');

//Ruta para añadir pelicula
Route::post('/administrador/movies', [AdminController::class, 'storeMovie'])->name('admin.storeMovie');

//Ruta para obtener las peliculas de la base de datos
Route::get('/administrador/manage-movies', [AdminController::class, 'obtenerPeliculas'])->name('obtenerPeliculas');

// Ruta para cambiar el estado 'activa' de una película específica por su ID
Route::patch('administrador/movies/{id}/estadoActivo', [AdminController::class, 'estadoPelicula'])->name('estadoPelicula');

//Ruta para cambiar de estreno a cartelera
Route::patch('administrador/movies/{id}/estrenoActivo', [AdminController::class, 'EstrenoStatus'])->name('EstrenoEstado');

//Ruta para obtener los menus de la base de datos
Route::get('administrador/menu', [AdminController::class, 'obtenerMenu'])->name('obtenerMenu');

//Ruta para cambiar el estado a activo o desactivado
Route::patch('administrador/menu/{id}/estadoActivo', [AdminController::class, 'estadoActivo'])->name('estadoActivo');

//Ruta para añadir nuevo elemento a la base de datos
Route::post('administrador/menu', [AdminController::class, 'añadirProducto'])->name('añadirProducto');

//Ruta para obtener detalles de cada producto para poderlos editar
Route::get('administrador/menu/{id}', [AdminController::class, 'obtenerProducto'])->name('obtenerProducto');

//Ruta para actualizar los detalles de cada producto
Route::put('administrador/menu/{id}', [AdminController::class, 'actualizarProducto'])->name('actualizarProducto');

//Ruta para activar peliculas en cartelera
Route::get('administrador/peliculas/activas-en-cartelera', [SessionController::class, 'getPeliculasActivasEnCartelera']);

// Ruta para obtener la lista de salas (para el select, aunque por ahora solo sea 1)
Route::get('administrador/salas', [SalaController::class, 'getSalas'])->name('admin.getSalas');

// Ruta para obtener las horas disponibles para una fecha, película y sala (para el select dinámico)
Route::get('administrador/sesiones/horas-disponibles', [SessionController::class, 'getHorasDisponibles'])->name('admin.getHorasDisponibles');

// Ruta para crear una nueva sesión
Route::post('administrador/sesiones', [SessionController::class, 'storeSesion'])->name('admin.storeSesion');

// Ruta para obtener las fechas disponibles
Route::get('administrador/fechas/disponibles', [FechaController::class, 'getFechasDisponibles'])->name('admin.getFechasDisponibles');

//Ruta para obtener sesione spor fecha
Route::get('administrador/sesiones-por-fecha/{fecha_id}', [SessionController::class, 'getSessionsByDate']);

//Ruta para eliminar sesiones
Route::delete('administrador/sesiones/{sesion_id}', [SessionController::class, 'deleteSession']);

// Recuperar las sesiones asociadas con una película
Route::get('/recuperar_sesiones/id_pelicula={peliculaId}', [RecuperarSesionPelicula::class, 'recuperar_sesion_pelicula']);

// Recuperar los asientos de la sesión seleccionada
Route::get('/recuperar_asientos/id_sesion={id_sesion}', [RecuperarAsientos::class, 'recuperar_asientos_sesion']);

// Ruta para procesar el envío del formulario
Route::post('administrador/users', [AdminController::class, 'crearEmpleado'])->name('users.store');

// Ruta para comprobar si el email existe
Route::get('administrador/check-email', [CheckController::class, 'checkEmail']);

// Ruta para comprobar si el DNI existe
Route::get('administrador/check-dni', [CheckController::class, 'checkDni']);

Route::get('/check-dni-profile', [CheckController::class, 'checkDni']);

//Gestionar nominas
Route::get('administrador/nomina/gestion', [AdminController::class, 'gestionarNominasIndex'])->name('nominas.gestion.index');

//Nomina del admin
Route::get('administrador/nomina/{idNomina}/pdf-admin-stream', [AdminController::class, 'generarPdfNominaAdmin'])->name('nomina.pdf.admin.stream');

//Descarga de la nomina del admin
Route::get('administrador/nomina/{idNomina}/pdf-admin-download', [AdminController::class, 'downloadNominaPdfAdmin'])->name('nomina.pdf.admin.download');

//Busqueda de nomina de usuario especifica
Route::get('administrador/empleado/{user}/nominas', [AdminController::class, 'gestionarNominasDeEmpleado'])->name('empleado.nominas.gestion');

Route::post('administrador/check-email-role', [AdminController::class, 'checkEmailRole'])->name('check.email.role');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('administrador.dashboard');
    })->name('dashboard');
});


// Recuperar los asientos de la sesión seleccionada
Route::get('/recuperar_asientos/id_sesion={id_sesion}', [RecuperarAsientos::class, 'recuperar_asientos_sesion']);

// Recuperar la sesión a través de una sesion_id
Route::get('/recuperar_sesion/id_sesion={id_sesion}', [RecuperarSesionPelicula::class, 'recuperar_sesion']);

// Rutas para autenticación con Google

Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('login.google');

Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);

//Gestionar nominas del empleado, obtener nominas y descargarlas.

Route::get('/empleado/nominas', [NominaEmpleadoController::class, 'showNominas'])->name('empleado.nominas.index');

Route::get('/empleado/nomina/{idNomina}/pdf', [NominaEmpleadoController::class, 'generarPdfNomina'])->name('empleado.nomina.pdf.stream');

Route::get('/empleado/nomina/{idNomina}/download', [NominaEmpleadoController::class, 'downloadNominaPdf'])->name('empleado.nomina.pdf.download');

// Gestionar el Pago y Creación de Entradas
Route::post('/procesar_pago', [ProcesarPago::class, 'procesar_pago'])->name('procesar_pago');

Route::get('/entrada/{id_entrada}/pdf', [EntradaController::class, 'descargarEntradaPdf'])
    ->name('entrada.pdf.download');

//Facturación

Route::get('administrador/reporte/diario', [FacturacionController::class, 'generarReporteDiarioPdf'])->name('pdf.diario');

Route::get('administrador/reporte/mensual', [FacturacionController::class, 'generarReporteMensualPdf'])->name('pdf.mensual');

Route::get('administrador/reporte/anual', [FacturacionController::class, 'generarReporteAnualPdf'])->name('pdf.anual');

    // Rutas para datos del Dashboard y AJAX
Route::get('administrador/facturacion/charts/ingresos-mensuales', [FacturacionController::class, 'datosIngresosMensuales'])->name('charts.ingresos');

Route::get('administrador/facturacion/resumen-hoy', [FacturacionController::class, 'getResumenHoy'])->name('resumen.hoy');

Route::get('administrador/facturacion/lista-facturas', [FacturacionController::class, 'getListaFacturas'])->name('lista');

    // Rutas de Pago - Redsys
Route::get('/redsys/payment-ok', [RedsysController::class, 'handle_ok'])->name('redsys_ok');

Route::get('/redsys/payment-ko', [RedsysController::class, 'handle_ko'])->name('redsys_ko');

Route::post('/redsys/webhook-notification', [RedsysController::class, 'handle_notification'])->name('redsys_notification');

    // Rutas de footer

Route::get('footer/politica_privacidad', [FooterController::class, 'politica_privacidad'])->name('footer_politica_privacidad');
Route::get('footer/terminos_y_condiciones', [FooterController::class, 'terminos_y_condiciones'])->name('footer_terminos_y_condiciones');
Route::get('footer/aviso_legal', [FooterController::class, 'aviso_legal'])->name('footer_aviso_legal');
Route::get('footer/politica_de_cookies', [FooterController::class, 'politica_de_cookies'])->name('footer_politica_de_cookies');
Route::get('footer/preguntas_frecuentes', [FooterController::class, 'preguntas_frecuentes'])->name('footer_preguntas_frecuentes');
Route::get('footer/contacto', [FooterController::class, 'contacto'])->name('footer_contacto');

