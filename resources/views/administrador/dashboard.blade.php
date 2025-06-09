<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Cosmos Cinema</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    @vite(['resources/css/dashboard.css'])
    @vite(['resources/js/adminDashboard.js'])
    @vite(['resources/js/adminDashboardGestionarPelicula.js'])
    @vite(['resources/js/adminDashboardGestionarMenu.js'])
    @vite(['resources/js/adminDashboardSesiones.js'])
    @vite(['resources/js/adminDashboardAñadirEmpleado.js'])
</head>

<body>
    <div class="dashboard-layout">
        <header class="dashboard-header">
            <button class="menu-toggle" aria-label="Abrir menú">☰</button>
            <div class="header-right-elements">
                <span class="admin-name">
                    @php
                    $adminName = '';
                    if (Auth::check()) {
                    $user = Auth::user();
                    $fullName = trim(($user->nombre ?? '') . ' ' . ($user->apellido ?? ''));
                    $adminName = $fullName ?: ($user->nombre_user_admin ?? $user->nombre ?? $user->email ?? 'Admin');
                    } else {
                    $adminName = 'Admin'; // O lo que quieras mostrar si no hay usuario logueado
                    }
                    @endphp
                    {{ $adminName }}
                </span>
                <div class="company-logo">
                    <img src="{{ asset('images/logoCosmosCinema.webp') }}" alt="Logo Empresa Cosmos Cinema">
                </div>
            </div>
        </header>

        <div class="main-dashboard-content">
            <aside class="dashboard-sidebar">
                <nav class="sidebar-nav">

                    <div class="sidebar-menu">
                        <ul>
                            <li>
                                <a href="#" class="sidebar-link active" data-section="add-movies">Añadir peliculas</a>
                            </li>
                            <li>
                                <a href="#" class="sidebar-link" data-section="manage-movies">Gestionar películas</a>
                            </li>
                            <li>
                                <a href="#" class="sidebar-link" id="menu" data-section="manage-menu">Gestionar Menú Cosmos</a>
                            </li>
                            <li>
                                <a href="#" class="sidebar-link" data-section="create-session">Gestionar Sesión</a>
                            </li>
                            <li>
                                <a href="#" class="sidebar-link" data-section="add-user">Añadir Empleado</a>
                            </li>
                            <li>
                                <a href="#" class="sidebar-link" data-section="facturacion">Facturación</a>
                            </li>
                        </ul>
                    </div>
                    <div class="sidebar-bottom">
                        <form action="{{ route('administrador.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="sidebar-logout-btn">Cerrar Sesión</button>
                        </form>
                    </div>
                </nav>
            </aside>

            <main class="dashboard-content-area">
                <section id="add-movies-section" class="content-section">
                    <h3>Añadir peliculas</h3>

                    <div class="api-search-filters">
                        <input type="text" id="api-search-input" placeholder="Buscar por título">
                        <select id="api-list-type-select">
                            <option value="search">Buscar por Título</option>
                            <option value="popular">Populares</option>
                            <option value="upcoming">Próximas</option>
                            <option value="now_playing">En cines</option>
                        </select>
                        <select id="api-genre-select">
                            <option value="">Todos los géneros</option>
                            @foreach ($generos_tmdb ?? [] as $genero)
                            <option value="{{ $genero['id'] }}">{{ $genero['name'] }}</option>
                            @endforeach
                        </select>
                        <select id="api-quantity-select">
                            <option value="1">20 películas (1 página)</option>
                            <option value="2">40 películas (2 páginas)</option>
                            <option value="3">60 películas (3 páginas)</option>
                            <option value="4">80 películas (4 páginas)</option>
                        </select>
                        <select id="api-language-select">
                            <option value="es">Español</option>
                            <option value="en">Inglés</option>
                        </select>
                        <button id="api-search-button">Buscar</button>
                    </div>

                    <hr>

                    <div class="api-results-area">
                        <p>Haz clic en "Buscar" para encontrar películas en TMDb.</p>
                    </div>

                    <div class="api-pagination-controls" style="text-align: center; margin-top: 20px;">
                        <button id="prev-page-btn" disabled>Anterior</button>
                        <span id="page-info">Página 1 de 1</span>
                        <button id="next-page-btn" disabled>Siguiente</button>
                    </div>

                </section>
                <section id="manage-movies-section" class="content-section hidden">
                    <h3>Gestionar películas en BD</h3>

                    <div class="manage-filters">
                        <input type="text" id="manage-search-input" placeholder="Buscar por título">
                        <select id="manage-genre-select">
                            <option value="">Todos los géneros</option>
                            @foreach ($generos_tmdb ?? [] as $genero)
                            <option value="{{ $genero['id'] }}">{{ $genero['name'] }}</option>
                            @endforeach
                        </select>
                        <select id="manage-status-select">
                            <option value="all">Todos los estados</option>
                            <option value="active">Activas</option>
                            <option value="inactive">Inactivas</option>
                        </select>
                        <select id="manage-items-per-page-select">
                            <option value="5">5 por página</option>
                            <option value="10">10 por página</option>
                            <option value="15">15 por página</option>
                            <option value="20">20 por página</option>
                        </select>
                        <button id="manage-filter-button">Aplicar Filtros</button>
                    </div>
                    <div id="modal-error-temporal" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: #fdd; border: 1px solid #faa; padding: 20px; border-radius: 5px; z-index: 1000; opacity: 0;">
                        <p style="color: #800;">No puedes activar una película si no tiene ninguna sesión ACTIVA programada.</p>
                    </div>
                    <div class="manage-movies-area">
                        <p>Selecciona filtros y haz clic en "Aplicar Filtros" para cargar la lista.</p>
                    </div>

                    <div class="manage-pagination-controls">
                        <button id="manage-prev-page-btn" disabled>Anterior</button>
                        <span id="manage-page-info">Página 0 de 0 (0 películas en total)</span>
                        <button id="manage-next-page-btn" disabled>Siguiente</button>
                    </div>
                </section>

                <section id="manage-menu-section" class="content-section hidden">
                    <h3>Gestionar Menú Cosmos</h3>

                    <div>
                        <button id="add-new-menu-item-button">Añadir Nuevo Elemento al Menú</button>
                    </div>

                    <div class="manage-menu-filters">
                        <input type="text" id="menu-search-input" placeholder="Buscar por nombre de producto">
                        <select id="menu-status-select">
                            <option value="all">Todos los estados</option>
                            <option value="active">Activos</option>
                            <option value="inactive">Inactivos</option>
                        </select>
                        <select id="menu-items-per-page-select">
                            <option value="5">5 por página</option>
                            <option value="10">10 por página</option>
                            <option value="15">15 por página</option>
                            <option value="20">20 por página</option>
                        </select>
                        <button id="menu-filter-button">Aplicar Filtros</button>
                    </div>

                    <div class="manage-menu-area">
                        <p>Cargando elementos del menú... o aplica filtros para buscar.</p>
                    </div>

                    <div class="menu-pagination-controls">
                        <button id="menu-prev-page-btn" disabled>Anterior</button>
                        <span id="menu-page-info">Página 0 de 0 (0 elementos en total)</span>
                        <button id="menu-next-page-btn" disabled>Siguiente</button>
                    </div>

                    <div id="menu-item-modal" class="modal">
                        <div class="modal-content">
                            <span class="close-button">&times;</span>
                            <h4 id="menu-item-modal-title">Añadir Elemento al Menú</h4>
                            <form id="menu-item-form" enctype="multipart/form-data">
                                <input type="hidden" id="menu-item-id" name="id">
                                <div>
                                    <label for="menu-item-nombre">Nombre:</label>
                                    <input type="text" id="menu-item-nombre" name="nombre" required>
                                </div>
                                <div>
                                    <label for="menu-item-descripcion">Descripción:</label>
                                    <textarea id="menu-item-descripcion" name="descripcion"></textarea>
                                </div>
                                <div>
                                    <label for="menu-item-precio">Precio:</label>
                                    <input type="number" id="menu-item-precio" name="precio" step="0.01" min="0" required>
                                </div>
                                <div>
                                    <label for="menu-item-foto">Foto:</label>
                                    <input type="file" id="menu-item-foto" name="foto" accept="image/*">
                                    <img id="menu-item-foto-preview" src="#" alt="Vista previa de la foto" style="max-width: 100px; max-height: 100px; display: none; margin-top: 10px;" />
                                    <input type="hidden" id="menu-item-current-foto-ruta" name="current_foto_ruta">
                                </div>
                                <div class="menu-form-messages" id="menu-form-message" style="display: none; text-align: center; margin-bottom: 15px; font-weight: bold;"></div>
                                <div id="botonesEditar">
                                    <button type="button" id="cancel-menu-item-button">Cancelar</button>
                                    <button type="submit" id="save-menu-item-button">Guardar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
                <section id="create-session-section" class="content-section hidden">
                    <h3>Gestión de Sesión de Películas</h3>
                    <div class="main-container">
                        <div class="form-section">
                            <h2>Crear nueva Sesión</h2>

                            <form id="create-session-form">
                                <div>
                                    <label for="session-fecha">Fecha:</label>
                                    <select id="session-fecha" name="fecha" required>
                                        <option value="">Seleccionar fecha</option>
                                        @if(isset($fechas))
                                        @foreach($fechas as $fecha)
                                        <option value="{{ $fecha->id }}">{{ $fecha->fecha }}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div>
                                    <label for="session-sala">Sala:</label>
                                    <select id="session-sala" name="sala_id" required>
                                        <option value="">Seleccionar sala</option>

                                    </select>
                                </div>
                                <div>
                                    <label for="session-pelicula">Película:</label>
                                    <select id="session-pelicula" name="pelicula_id" required>
                                        <option value="">Seleccionar película</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="session-hora">Hora:</label>
                                    <select id="session-hora" name="hora" required>
                                        <option value="">Seleccionar hora</option>
                                    </select>
                                </div>
                                <div>
                                    <button type="submit" class="button primary">Crear Sesión</button>
                                </div>
                                <div id="session-creation-message" style="margin-top: 10px; padding: 10px; text-align: center; font-weight: bold;"></div>
                            </form>
                        </div>

                        <div class="divider"></div>
                        <div class="sessions-table-section">
                            <h2>Sesiones Creadas<span id="selected-session-date"></span></h2>
                            <p id="noSessionsMessage" style="display: none;">No hay sesiones creadas para esta fecha.</p>
                            <div class="table-responsive-container">
                                <table id="sessionsTable" class="sessions-table">
                                    <thead>
                                        <tr>
                                            <th>Sesión</th>
                                            <th>Película</th>
                                            <th>Hora</th>
                                            <th>Hora Final</th>
                                            <th>Sala</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="add-user-section" class="content-section hidden">
                    <div class="add-user-form-container">

                        <h3>Añadir Nuevo Empleado</h3>

                        <form action="{{ route('users.store') }}" id="add-user-form" method="POST">
                            @csrf {{-- Token CSRF --}}

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nombre" class="form-label">Nombre</label>
                                        <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" value="{{ old('nombre') }}" placeholder="Nombre" required>
                                        @error('nombre')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <span class="client-side-field-error" style="color: red; font-size: 0.8em; display: none;"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="apellidos" class="form-label">Apellidos</label>
                                        <input type="text" class="form-control @error('apellidos') is-invalid @enderror" id="apellidos" name="apellidos" value="{{ old('apellidos') }}" placeholder="Apellidos" required>
                                        @error('apellidos')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <span class="client-side-field-error" style="color: red; font-size: 0.8em; display: none;"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="codigo_postal" class="form-label">Código Postal</label>
                                        <input type="text" class="form-control" id="codigo_postal" name="codigo_postal" required maxlength="5" minlength="5" placeholder="00000">
                                        <div class="client-side-field-error" style="display: none; color: red;"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                        <input type="date" class="form-control @error('fecha_nacimiento') is-invalid @enderror" id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" required>
                                        @error('fecha_nacimiento')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <span class="client-side-field-error" style="color: red; font-size: 0.8em; display: none;"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="numero_telefono" class="form-label">Número de Teléfono</label>
                                        <input type="text" class="form-control @error('numero_telefono') is-invalid @enderror" id="numero_telefono" name="numero_telefono" value="{{ old('numero_telefono') }}" pattern="^\d{9}$" title="El teléfono debe tener 9 dígitos." maxlength="9" minlength="9" placeholder="000000000" required>
                                        @error('numero_telefono')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <span class="client-side-field-error" style="color: red; font-size: 0.8em; display: none;"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="dni" class="form-label">DNI</label>
                                        <input type="text" class="form-control @error('dni') is-invalid @enderror" id="dni" name="dni" value="{{ old('dni') }}" pattern="^\d{8}[A-Za-z]$" title="El DNI debe tener 8 dígitos seguidos de una letra." maxlength="9" minlength="9" placeholder="00000000X" required>
                                        @error('dni')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <span class="client-side-field-error" style="color: red; font-size: 0.8em; display: none;"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="ciudad_id" class="form-label">Ciudad</label>
                                        <select class="form-select @error('ciudad_id') is-invalid @enderror" id="ciudad" name="ciudad" required>
                                            <option value="" disabled selected>Selecciona la ciudad</option>
                                            @isset($ciudades)
                                            @foreach($ciudades as $ciudad)
                                            <option value="{{ $ciudad->id }}" {{ old('ciudad_id') == $ciudad->id ? 'selected' : '' }}>
                                                {{ $ciudad->nombre }}
                                            </option>
                                            @endforeach
                                            @endisset
                                        </select>
                                        @error('ciudad_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <span class="client-side-field-error" style="color: red; font-size: 0.8em; display: none;"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tipo_usuario" class="form-label">Tipo de Usuario</label>
                                        <select class="form-select" id="tipo_usuario" name="tipo_usuario">
                                            <option value="">Seleccionar tipo</option>
                                            <option value="1">Administrador</option>
                                            <option value="2">Empleado</option>
                                        </select>
                                        @error('tipo_usuario')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <span class="client-side-field-error" style="color: red; font-size: 0.8em; display: none;"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Correo Electrónico</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="correo@ejemplo.com" required>
                                        @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <span class="client-side-field-error" style="color: red; font-size: 0.8em; display: none;"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- Fila/Div para el Botón de Submit --}}
                            <div class="mb-3 text-center">
                                <button type="submit" class="btn btn-primary">Generar Empleado</button>
                            </div>

                            <div id="user-creation-message" style="margin-top: 10px; padding: 10px; text-align: center; font-weight: bold; display: none;">
                            </div>

                        </form>

                    </div>
                </section>

                <section id="facturacion-section" class="content-section hidden">
                    <h3>Facturación</h3>

                    <!-- Contenido del Dashboard de Facturación -->
                    <div class="container-fluid"> {{-- Usamos container-fluid para que ocupe el ancho --}}

                        <!-- Fila 1: Resumen del Día/Periodo y Gráfico Principal -->
                        <div class="row mb-4">
                            <div class="col-md-4" id="tamaño">
                                <div class="card">
                                    <div class="card-header">
                                        Resumen Hoy ({{ now()->format('d/m/Y') }})
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Ingresos Brutos (Total):</strong> <span id="fact-resumen-bruto-hoy">Cargando...</span></p>
                                        <p><strong>Total Impuestos:</strong> <span id="fact-resumen-impuestos-hoy">Cargando...</span></p>
                                        <p><strong>Ingresos Netos (Base):</strong> <span id="fact-resumen-neto-hoy">Cargando...</span></p>
                                        <p><strong>Nº Facturas:</strong> <span id="fact-resumen-num-facturas-hoy">Cargando...</span></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        Ingresos Mensuales (Año: <span id="fact-chartYear">{{ now()->year }}</span>)
                                    </div>
                                    <div class="card-body">
                                        <form id="fact-chartYearForm" class="row gx-2 gy-2 align-items-center mb-3">
                                            <div class="col-auto">
                                                <label for="fact-select_chart_year" class="visually-hidden">Año</label>
                                                <select name="ano" id="fact-select_chart_year" class="form-select form-select-sm">
                                                    @for ($year = now()->year; $year >= now()->year - 5; $year--) {{-- Ajusta el rango --}}
                                                    <option value="{{ $year }}" {{ $year == now()->year ? 'selected' : '' }}>{{ $year }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                            <div class="col-auto">
                                                <button type="submit" class="btn btn-primary btn-sm">Actualizar Gráfico</button>
                                            </div>
                                        </form>
                                        <div style="height: 300px;"> {{-- Contenedor con altura fija para el canvas --}}
                                            <canvas id="fact-ingresosMensualesChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Fila 2: Generación de Reportes PDF (MODIFICADA) -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        Generar Reportes PDF
                                    </div>
                                    <div class="card-body">
                                        <form id="fact-report-generator-form" class="row gx-3 gy-3 align-items-end">
                                            <div class="col-md-3">
                                                <label for="fact-report-type" class="form-label">Tipo de Reporte:</label>
                                                <select id="fact-report-type" class="form-select form-select-sm">
                                                    <option value="diario" selected>Diario</option>
                                                    <option value="mensual">Mensual</option>
                                                    <option value="anual">Anual</option>
                                                </select>
                                            </div>

                                            <!-- Controles para Reporte Diario -->
                                            <div class="col-md-3 fact-report-controls" id="fact-diario-controls">
                                                <label for="fact-report-fecha_diario" class="form-label">Seleccionar Fecha:</label>
                                                <input type="date" name="fecha" id="fact-report-fecha_diario" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" required>
                                            </div>

                                            <!-- Controles para Reporte Mensual (inicialmente ocultos) -->
                                            <div class="col-md-2 fact-report-controls" id="fact-mensual-controls-mes" style="display: none;">
                                                <label for="fact-report-mes_mensual" class="form-label">Mes:</label>
                                                <select name="mes" id="fact-report-mes_mensual" class="form-select form-select-sm" required>
                                                    @for ($m=1; $m<=12; $m++)
                                                        <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                                                        @endfor
                                                </select>
                                            </div>
                                            <div class="col-md-2 fact-report-controls" id="fact-mensual-controls-ano" style="display: none;">
                                                <label for="fact-report-ano_mensual" class="form-label">Año:</label>
                                                <select name="ano_mensual" id="fact-report-ano_mensual" class="form-select form-select-sm" required>
                                                    @for ($year = now()->year; $year >= now()->year - 5; $year--)
                                                    <option value="{{ $year }}" {{ $year == now()->year ? 'selected' : '' }}>{{ $year }}</option>
                                                    @endfor
                                                </select>
                                            </div>

                                            <!-- Controles para Reporte Anual (inicialmente ocultos) -->
                                            <div class="col-md-3 fact-report-controls" id="fact-anual-controls" style="display: none;">
                                                <label for="fact-report-ano_anual" class="form-label">Año:</label>
                                                <select name="ano_anual" id="fact-report-ano_anual" class="form-select form-select-sm" required>
                                                    @for ($year = now()->year; $year >= now()->year - 5; $year--)
                                                    <option value="{{ $year }}" {{ $year == now()->year ? 'selected' : '' }}>{{ $year }}</option>
                                                    @endfor
                                                </select>
                                            </div>

                                            <div class="col-md-auto"> {{-- Ajustado para que el botón se alinee mejor --}}
                                                <button type="button" id="fact-generate-pdf-btn" class="btn btn-info btn-sm w-100">Generar PDF</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Fila 3: Listado de Últimas Facturas (Opcional, o con filtros) -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        Últimas Facturas Registradas
                                        <form id="fact-filter-form" class="float-end row gx-2 gy-2 align-items-center">
                                            <div class="col-auto">
                                                <input type="date" name="fecha_desde" id="fact-filter-fecha_desde" class="form-control form-control-sm" placeholder="Desde">
                                            </div>
                                            <div class="col-auto">
                                                <input type="date" name="fecha_hasta" id="fact-filter-fecha_hasta" class="form-control form-control-sm" placeholder="Hasta">
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" id="fact-apply-filters-btn" class="btn btn-secondary btn-sm">Filtrar</button>
                                                <button type="button" id="fact-clear-filters-btn" class="btn btn-outline-secondary btn-sm">Limpiar</button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>ID Factura</th>
                                                        <th>Fecha</th>
                                                        <th>Impuesto</th>
                                                        <th class="text-end">Neto (Base)</th>
                                                        <th class="text-end">Impuesto</th>
                                                        <th class="text-end">Bruto (Total)</th>
                                                        {{-- <th>Acciones</th> --}}
                                                    </tr>
                                                </thead>
                                                <tbody id="fact-facturas-list">
                                                    {{-- Las filas de facturas se cargarán aquí vía AJAX o con paginación de Laravel --}}
                                                    <tr>
                                                        <td colspan="7" class="text-center">Cargando facturas...</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <nav aria-label="Paginación de facturas">
                                            <ul class="pagination pagination-sm justify-content-center" id="fact-pagination-links">
                                                {{-- Los enlaces de paginación se cargarán aquí --}}
                                            </ul>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </section>

            </main>
        </div>
    </div>
</body>

</html>