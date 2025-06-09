<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Nóminas - Cosmos Cinema</title> {{-- Título adaptado para el empleado --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    @vite(['resources/css/dashboard.css'])
</head>

<body>
    <div class="dashboard-layout">
        {{-- Reutilizar el header del dashboard --}}
        <header class="dashboard-header">
            <button class="menu-toggle" aria-label="Abrir menú">☰</button>
            <div class="header-right-elements">
                <span class="admin-name"> {{-- El nombre del usuario logueado --}}
                    @php
                    $userName = '';
                    if (Auth::check()) {
                    $user = Auth::user();
                    $fullName = trim(($user->nombre ?? '') . ' ' . ($user->apellidos ?? ''));
                    $userName = $fullName ?: ($user->email ?? 'Usuario'); // Fallback a email o 'Usuario'
                    } else {
                    $userName = 'Usuario';
                    }
                    @endphp
                    {{ $userName }}
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
                                <a href="{{ route('empleado.nominas.index') }}" class="sidebar-link active">Mis Nóminas</a>
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
                {{-- Esta sección contendrá el contenido específico de 'Mis Nóminas' --}}
                <section id="mis-nominas-section" class="content-section">
                    <h3>Mis Nóminas</h3>

                    <form method="GET" action="{{ route('empleado.nominas.index') }}" class="filters-form">
                        @csrf {{-- Aunque es GET, @csrf es buena práctica --}}
                        <div class="row g-3"> {{-- Usar clases de Bootstrap para layout --}}
                            <div class="col-md-6">
                                <label for="filter_mes" class="form-label">Filtrar por Mes:</label>
                                <select class="form-select" id="filter_mes" name="mes">
                                    <option value="">Todos los Meses</option>
                                    @php
                                    $meses = [
                                    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                                    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                                    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                                    ];
                                    $selectedMonth = request('mes');
                                    @endphp
                                    @foreach ($meses as $num => $nombre)
                                    <option value="{{ $num }}" {{ $selectedMonth == $num ? 'selected' : '' }}>
                                        {{ $nombre }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="filter_anio" class="form-label">Filtrar por Año:</label>
                                <select class="form-select" id="filter_anio" name="anio">
                                    <option value="">Todos los Años</option>
                                    @php
                                    $currentYear = date('Y');
                                    $startYear = $currentYear - 10; // Por ejemplo, últimos 10 años
                                    $selectedYear = request('anio');
                                    @endphp
                                    @for ($year = $currentYear; $year >= $startYear; $year--)
                                    <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                    @endfor
                                </select>
                            </div>


                            <div class="col-md-4">
                                <label for="filter_fecha_inicio" class="form-label">Desde:</label>
                                <input type="date" class="form-control" id="filter_fecha_inicio" name="fecha_inicio" value="{{ request('fecha_inicio') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="filter_fecha_fin" class="form-label">Hasta:</label>
                                <input type="date" class="form-control" id="filter_fecha_fin" name="fecha_fin" value="{{ request('fecha_fin') }}">
                            </div>



                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Aplicar Filtros</button>
                            </div>
                        </div>
                    </form>
                    <hr>

                    @if($nominas->isEmpty())
                    <p>No se encontraron nóminas con los filtros aplicados.</p>
                    @else
                    <div class="table-responsive-container">
                        <table class="table sessions-table">
                            <thead>
                                <tr>
                                    <th>Período</th>
                                    <th>Salario Bruto</th>
                                    <th>Salario Neto</th>
                                    <th>Fecha Generación</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($nominas as $nomina)
                                <tr>
                                    <td>{{ $nomina->periodoCompleto }}</td>

                                    <td>{{ number_format($nomina->salario_bruto, 2, ',', '.') }} €</td>

                                    <td>{{ $nomina->salarioNetoFormateado }}</td>

                                    <td>{{ $nomina->generacion_fecha->format('d/m/Y') }}</td>

                                    <td>
                                        <a href="{{ route('empleado.nomina.pdf.download', ['idNomina' => $nomina->id]) }}" class="btn btn-success btn-sm" target="_blank">Descargar PDF</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    @if(!$nominas->isEmpty() && $nominas->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $nominas->appends(request()->query())->links() }} {{-- appends para mantener filtros --}}
                    </div>
                    @endif

                </section>
            </main>
        </div>
    </div>

    {{-- Incluir los scripts JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    @vite(['resources/js/adminDashboard.js'])

</body>

</html>