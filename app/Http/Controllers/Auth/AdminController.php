<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Administrator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Http\Controllers\PeticionPeliculasController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Rules\letraDNI;
use App\Models\Pelicula;
use App\Models\User;
use App\Models\Ciudad;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use App\Models\NominaEmpleados;
use App\Mail\CredencialesEmpleado;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    public function mostrarLogin(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('administrador.dashboard');
        }

        $user = Auth::guard('web')->user();

        return view('administrador.loginAdministrador', compact('user'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        /* $recaptchaResponse = $request->input('g-recaptcha-response');
        if (empty($recaptchaResponse)) {
            return back()->withErrors(['recaptcha' => 'Por favor, completa el desafío reCAPTCHA.'])->onlyInput('email');
        }
        $verificationUrl = 'https://www.google.com/recaptcha/api/siteverify';
        $response = Http::asForm()->post($verificationUrl, [
            'secret' => env('RECAPTCHA_SECRET_KEY'),
            'response' => $recaptchaResponse,
            'remoteip' => $request->ip(),
        ]);
        $recaptchaResult = $response->json();
        if (!isset($recaptchaResult['success']) || !$recaptchaResult['success']) {
            return back()->withErrors(['recaptcha' => 'La verificación reCAPTCHA falló. Inténtalo de nuevo.'])->onlyInput('email');
        } */

        $credentials = $request->only('email', 'password');

        if (Auth::guard('web')->attempt($credentials, $request->filled('remember'))) {
            $user = Auth::guard('web')->user();

            if ($user->tipo_usuario == 1) { // Asumiendo 1 es Admin
                if (!$request->has('codigo_administrador') || empty($request->codigo_administrador)) {
                    Auth::guard('web')->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return back()->withErrors([
                        'email' => 'Credenciales incompletas. Se requiere código de administrador.',
                    ])->onlyInput('email');
                }

                $request->validate([
                    'codigo_administrador' => 'required|string',
                ]);

                $administratorModelInstance = Administrator::where('email', $user->email)->first();

                if ($administratorModelInstance && $request->codigo_administrador === $administratorModelInstance->codigo_administrador) {
                    Auth::guard('web')->logout();
                    Auth::guard('admin')->login($administratorModelInstance, $request->filled('remember'));
                    $request->session()->regenerate();
                    return redirect()->intended(route('administrador.dashboard'));
                } else {
                    Auth::guard('web')->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return back()->withErrors([
                        'codigo_administrador' => 'El código de administrador proporcionado es incorrecto.',
                    ])->onlyInput('email', 'codigo_administrador');
                }
            } elseif ($user->tipo_usuario == 2) { // Asumiendo 2 es Empleado
                $request->session()->regenerate();
                return redirect()->intended(route('empleado.nominas.index'));
            } else {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors([
                    'email' => 'No tienes los permisos necesarios para acceder a esta sección.',
                ])->onlyInput('email');
            }
        } else {
            return back()->withErrors([
                'email' => 'Las credenciales proporcionadas no coinciden.',
            ])->onlyInput('email');
        }
    }


    public function checkEmailRole(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;

        $user = User::where('email', $email)->first();

        $is_admin = false;

        if ($user) {
            if ($user->tipo_usuario == 1) {
                $is_admin = true;
            }
        }

        return response()->json(['is_admin' => $is_admin]);
    }

    public function searchTMDb(Request $request)
    {
        $query = $request->input('query');
        $listType = $request->input('list_type', 'search');
        $genreId = $request->input('genre_id');
        $pagesToFetch = $request->input('pages_to_fetch', 1);
        $searchLanguage = $request->input('language', 'en');

        $apiKey = config('services.tmdb.api_key');

        if (!$apiKey) {
            return response()->json(['error' => 'API Key no configurada en el backend'], 500);
        }

        $allMovies = [];

        for ($page = 1; $page <= $pagesToFetch; $page++) {
            $apiUrl = '';
            $apiParams = [
                'api_key' => $apiKey,
                'language' => $searchLanguage,
                'page' => $page,
            ];

            if ($listType === 'search') {
                if (empty($query)) {
                    continue;
                }
                $apiUrl = "https://api.themoviedb.org/3/search/movie";
                $apiParams['query'] = $query;
            } elseif ($listType === 'popular') {
                $apiUrl = "https://api.themoviedb.org/3/movie/popular";
                if (!empty($genreId)) {
                    $apiUrl = "https://api.themoviedb.org/3/discover/movie";
                    $apiParams['with_genres'] = $genreId;
                }
            } elseif ($listType === 'now_playing') {
                $apiUrl = "https://api.themoviedb.org/3/movie/now_playing";
                if (!empty($genreId)) {
                    $apiUrl = "https://api.themoviedb.org/3/discover/movie";
                    $apiParams['with_genres'] = $genreId;
                }
            } elseif ($listType === 'upcoming') {
                $apiUrl = "https://api.themoviedb.org/3/movie/upcoming";
                if (!empty($genreId)) {
                    $apiUrl = "https://api.themoviedb.org/3/discover/movie";
                    $apiParams['with_genres'] = $genreId;
                    $apiParams['primary_release_date.gte'] = now()->toDateString();
                    $apiParams['sort_by'] = 'primary_release_date.asc';
                }
            } else {
                if ($page === 1) {
                    return response()->json(['error' => 'Tipo de lista no soportado.'], 400);
                }
                continue;
            }

            $response = Http::get($apiUrl, $apiParams);

            if ($response->successful()) {
                $data = $response->json();
                $allMovies = array_merge($allMovies, $data['results'] ?? []);

                if (!isset($data['total_pages']) || !is_numeric($data['total_pages']) || ($data['total_pages'] <= $page)) {
                    break;
                }
            } else {
                $statusCode = $response->status();
                $errorMessage = $response->body();
                Log::error("Error al llamar a TMDb API (Página {$page}): Estatus {$statusCode}, Mensaje: {$errorMessage}");
                break;
            }
        }

        $tmdbIds = collect($allMovies)->pluck('id')->filter()->unique()->toArray();

        if (empty($tmdbIds)) {
            return response()->json([]);
        }

        $existingMovieIds = Pelicula::whereIn('id_api', $tmdbIds)
            ->pluck('id_api')
            ->toArray();

        $existingMovieIdsMap = array_fill_keys($existingMovieIds, true);

        $moviesWithStatus = collect($allMovies)->map(function ($movie) use ($existingMovieIdsMap) {
            $movie['is_added'] = array_key_exists($movie['id'], $existingMovieIdsMap);
            return $movie;
        })->values()->all();

        return response()->json($moviesWithStatus);
    }

    public function storeMovie(Request $request)
    {
        $request->validate([
            'tmdb_id' => 'required|integer|min:1',
        ]);

        $tmdbId = $request->input('tmdb_id');

        $existingMovie = Pelicula::where('id_api', $tmdbId)->first();

        if ($existingMovie) {
            return response()->json(['message' => 'Esta película ya ha sido añadida.', 'status' => 'duplicate'], 409);
        }

        $apiKey = config('services.tmdb.api_key');
        
        if (!$apiKey) {
            return response()->json(['error' => 'API Key no configurada en el backend'], 500);
        }

        $apiUrlDetails = "https://api.themoviedb.org/3/movie/{$tmdbId}";
        $apiParamsDetails = [
            'api_key' => $apiKey,
            'language' => 'es',
        ];

        $responseDetails = Http::get($apiUrlDetails, $apiParamsDetails);

        if (!$responseDetails->successful()) {
            $statusCode = $responseDetails->status();
            $errorMessage = $responseDetails->body();
            return response()->json([
                'error' => 'No se pudieron obtener los detalles completos de la película de TMDb.',
                'details' => $errorMessage
            ], $statusCode >= 400 ? $statusCode : 500);
        }

        $movieDetails = $responseDetails->json();

        try {
            $releaseDate = null;
            if (!empty($movieDetails['release_date'])) {
                try {
                    $releaseDate = Carbon::parse($movieDetails['release_date'])->toDateString();
                } catch (\Exception $e) {
                    $releaseDate = null;
                }
            }

            $movieToSave = new Pelicula();
            $movieToSave->id_api = $movieDetails['id'];
            $movieToSave->titulo = $movieDetails['title'] ?? $movieDetails['original_title'] ?? 'Título desconocido';
            $movieToSave->titulo_original = $movieDetails['original_title'] ?? null;
            $movieToSave->sinopsis = $movieDetails['overview'] ?? null;
            $movieToSave->fecha_estreno = $releaseDate;
            $movieToSave->poster_ruta = $movieDetails['poster_path'] ?? null;
            $movieToSave->backdrop_ruta = $movieDetails['backdrop_path'] ?? null;
            $movieToSave->adult = $movieDetails['adult'] ?? false;
            $movieToSave->video = $movieDetails['video'] ?? false;

            $movieToSave->duracion = $movieDetails['runtime'] ?? null;
            $movieToSave->puntuacion_promedio = $movieDetails['vote_average'] ?? 0;
            $movieToSave->numero_votos = $movieDetails['vote_count'] ?? 0;
            $movieToSave->popularidad = $movieDetails['popularity'] ?? 0;

            $spokenLanguages = $movieDetails['spoken_languages'] ?? [];
            $spokenLanguageCodes = collect($spokenLanguages)->pluck('iso_639_1')->toArray();
            $movieToSave->lenguaje_original = $spokenLanguageCodes[0] ?? null;

            $movieToSave->activa = $request->input('activa', false);
            $movieToSave->estreno = $request->input('estreno', true);

            $movieToSave->save();

            return response()->json([
                'message' => 'Película añadida con éxito.',
                'status' => 'success',
                'movie' => $movieToSave
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ocurrió un error al guardar la película.', 'details' => $e->getMessage()], 500);
        }
    }

    public function obtenerPeliculas(Request $request)
    {
        $query = $request->input('query');
        $status = $request->input('status', 'all');
        $itemsPerPage = $request->input('items_per_page', 10);
        $itemsPerPage = (int) $itemsPerPage;

        $itemsPerPage = max(1, min(50, $itemsPerPage));

        $movies = Pelicula::query();

        if (!empty($query)) {
            $movies->where(function ($q) use ($query) {
                $q->where('titulo', 'like', '%' . $query . '%')
                    ->orWhere('titulo_original', 'like', '%' . $query . '%');
            });
        }

        if ($status === 'active') {
            $movies->where('activa', true);
        } elseif ($status === 'inactive') {
            $movies->where('activa', false);
        }

        $paginatedMovies = $movies->paginate($itemsPerPage);

        return response()->json($paginatedMovies);
    }

    public function estadoPelicula(Request $request, $id)
{
    $movie = Pelicula::findOrFail($id);
    $nuevoEstadoActiva = !$movie->activa;

    Log::info("DEBUG: estadoPelicula - ID: {$id}, Activa: " . ($movie->activa ? 'true' : 'false') . ", Intento Activar: " . ($nuevoEstadoActiva ? 'true' : 'false') . ", Estreno: " . ($movie->estreno ? 'true' : 'false'));

    if ($nuevoEstadoActiva === true) {
        if ($movie->estreno === false) {
            Log::info("DEBUG: estadoPelicula - Es activación de cartelera.");
            if (!$movie->sesiones()->exists()) {
                Log::info("DEBUG: estadoPelicula - Cartelera sin sesiones. Bloqueando.");
                return response()->json([
                    'message' => 'No se puede activar la película en cartelera porque no tiene ninguna sesión programada.',
                    'error_type' => 'validation',
                    'movie_id' => $movie->id
                ], 422);
            } else {
                Log::info("DEBUG: estadoPelicula - Cartelera con sesiones.");
            }
        } else {
            Log::info("DEBUG: estadoPelicula - Es activación de estreno.");
        }
    } else {
        Log::info("DEBUG: estadoPelicula - Es desactivación.");
    }

    $movie->activa = $nuevoEstadoActiva;
    $movie->save();
    Log::info("DEBUG: estadoPelicula - Nuevo estado 'activa' guardado: " . ($movie->activa ? 'true' : 'false'));

    return response()->json([
        'message' => 'Estado de película actualizado con éxito.',
        'new_status' => $movie->activa,
        'movie_id' => $movie->id
    ]);
}

    public function EstrenoStatus($id)
    {
        $movie = Pelicula::findOrFail($id);

        $movie->estreno = !$movie->estreno;

        $movie->save();

        $newStatusText = $movie->estreno ? 'Estreno' : 'Cartelera';

        return response()->json([
            'message' => "Estado de cartelera/estreno actualizado a '{$newStatusText}'.",
            'new_status' => $movie->estreno,
            'new_status_text' => $newStatusText
        ]);
    }

    public function obtenerMenu(Request $request)
    {
        try {
            $query = MenuItem::orderBy('nombre');

            if ($request->has('search')) {
                $searchTerm = $request->input('search');
                $query->where('nombre', 'like', '%' . $searchTerm . '%')
                    ->orWhere('descripcion', 'like', '%' . $searchTerm . '%');
            }

            if ($request->has('status') && $request->input('status') !== 'all') {
                $status = $request->input('status');
                $query->where('activo', $status);
            }

            $perPage = $request->input('perPage', 10);
            $menuItems = $query->paginate($perPage);

            return response()->json($menuItems);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ocurrió un error al cargar los elementos del menú.'], 500);
        }
    }

    public function obtenerProducto($id)
    {
        $menuItem = MenuItem::findOrFail($id);

        return response()->json($menuItem);
    }

    public function actualizarProducto(Request $request, $id)
    {
        $menuItem = MenuItem::findOrFail($id);

        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
        ]);

        $menuItem->nombre = $validatedData['nombre'];
        $menuItem->descripcion = $validatedData['descripcion'];
        $menuItem->precio = $validatedData['precio'];

        $menuItem->save();

        return response()->json([
            'message' => 'Elemento del menú actualizado con éxito.',
            'item' => $menuItem
        ]);
    }

    public function añadirProducto(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'foto' => 'nullable|image|max:2048',
        ]);

        $menuItem = new MenuItem(); // O new Menu()

        $menuItem->nombre = $validatedData['nombre'];
        $menuItem->descripcion = $validatedData['descripcion'];
        $menuItem->precio = $validatedData['precio'];
        $menuItem->activo = true;

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('images/menus');

            $file->move($destinationPath, $filename);

            $menuItem->imagen_url = '/images/menus/' . $filename;
        } else {
            $menuItem->imagen_url = '/images/menus/imagenDefecto.webp';
        }

        $menuItem->save();

        return response()->json([
            'message' => 'Elemento del menú añadido con éxito.',
            'item' => $menuItem
        ], 201);
    }

    public function estadoActivo($id)
    {
        try {
            $menuItem = MenuItem::findOrFail($id);

            $menuItem->activo = !$menuItem->activo;

            $menuItem->save();

            $newStatusText = $menuItem->activo ? 'Activado' : 'Desactivado';

            return response()->json([
                'message' => "Estado de elemento del menú actualizado a '{$newStatusText}'.",
                'new_status' => $menuItem->activo,
                'new_status_text' => $newStatusText,
                'item_id' => $menuItem->id
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ocurrió un error al actualizar el estado del elemento del menú.'], 500);
        }
    }

    public function crearEmpleado(Request $request)
{
    Log::info('Inicio de crearEmpleado');

    $mensajes = [
        'nombre.required' => 'El campo Nombre es obligatorio.',
        'nombre.string' => 'El campo Nombre debe ser una cadena de texto.',
        'nombre.max' => 'El campo Nombre no debe exceder los :max caracteres.',
        'apellidos.required' => 'El campo Apellidos es obligatorio.',
        'apellidos.string' => 'El campo Apellidos debe ser una cadena de texto.',
        'apellidos.max' => 'El campo Apellidos no debe exceder los :max caracteres.',
        'ciudad.required' => 'Debes seleccionar una ciudad.',
        'ciudad.exists' => 'La ciudad seleccionada no es válida.',
        'codigo_postal.required' => 'El campo Código Postal es obligatorio.',
        'codigo_postal.string' => 'El campo Código Postal debe ser una cadena de texto.',
        'codigo_postal.max' => 'El Código Postal debe tener exactamente :max dígitos.',
        'codigo_postal.min' => 'El Código Postal debe tener exactamente :min dígitos.',
        'numero_telefono.required' => 'El campo Número de Teléfono es obligatorio.',
        'numero_telefono.digits' => 'El campo Número de Teléfono debe tener exactamente :digits dígitos.',
        'dni.required' => 'El campo DNI es obligatorio.',
        'dni.regex' => 'El formato del DNI debe ser 8 números seguidos de una letra.',
        'dni.unique' => 'Ya existe un usuario con este DNI registrado.',
        'fecha_nacimiento.required' => 'El campo Fecha de Nacimiento es obligatorio.',
        'fecha_nacimiento.date' => 'El campo Fecha de Nacimiento debe ser una fecha válida.',
        'fecha_nacimiento.before' => 'El campo Fecha de Nacimiento debe ser una fecha anterior a :date.',
        'tipo_usuario.required' => 'Debes seleccionar el tipo de usuario.',
        'tipo_usuario.integer' => 'El campo Tipo de Usuario debe ser un número entero.',
        'tipo_usuario.in' => 'El tipo de usuario seleccionado no es válido.',
        'email.required' => 'El campo Correo Electrónico es obligatorio.',
        'email.email' => 'El formato del Correo Electrónico no es válido.',
    ];
    $validator = Validator::make($request->all(), [
        'nombre' => ['required', 'string', 'max:100'],
        'apellidos' => ['required', 'string', 'max:100'],
        'ciudad' => ['required', 'exists:ciudades,id'],
        'codigo_postal' => ['required', 'string', 'max:5', 'min:5'],
        'numero_telefono' => ['required', 'digits:9'],
        'dni' => ['required', 'regex:/^\d{8}[A-Za-z]$/', 'unique:users,dni', new letraDNI],
        'fecha_nacimiento' => ['required', 'date', 'before:today'],
        'tipo_usuario' => ['required', 'integer', 'in:1,2'],
        'email' => ['required', 'email'], // Validamos que el email del input sea correcto
    ], $mensajes);

    Log::info('Validación realizada', ['fails' => $validator->fails(), 'errors' => $validator->errors()->all(), 'validated_data' => $request->all()]);

    if ($validator->fails()) {
        $ciudades = Ciudad::orderBy('nombre')->get();
        Log::info('La validación falló. Redireccionando.');
        return back()->withErrors($validator)->withInput()->with(compact('ciudades'));
    }

    $validatedData = $validator->validated();
    $emailParaCorreo = $validatedData['email']; // Email del input para enviar el correo

    $normalizeEmailPart = function ($string) {
        $string = Str::lower($string);
        $string = str_replace(' ', '', $string);
        $string = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ', 'Ü'], ['a', 'e', 'i', 'o', 'u', 'n', 'u', 'A', 'E', 'I', 'O', 'U', 'N', 'U'], $string);
        $string = preg_replace('/[^a-z0-9]/', '', $string);
        return $string;
    };

    $nombreNormalizado = $normalizeEmailPart($validatedData['nombre']);
    $apellidosNormalizado = $normalizeEmailPart($validatedData['apellidos']);
    $letraDni = Str::upper(substr($validatedData['dni'], -1));

    $generatedEmail = $nombreNormalizado . '.' . $apellidosNormalizado . '.' . $letraDni . '@cosmosAdmin.com';

    $originalEmail = $generatedEmail;
    $counter = 1;
    while (User::where('email', $generatedEmail)->exists()) {
        $generatedEmail = $originalEmail . $counter;
        $counter++;
    }

    $defaultPassword = '';
    $esAdmin = false;
    $nombreAdminUsuario = null;
    $codigoAdminGenerado = null;

    if ($validatedData['tipo_usuario'] == 1) {
        $defaultPassword = Str::random(12); // Generar contraseña aleatoria segura
        $esAdmin = true;
    } else if ($validatedData['tipo_usuario'] == 2) {
        $defaultPassword = Str::random(12); // Generar contraseña aleatoria segura
        $esAdmin = false;
    }

    $hashedPassword = Hash::make($defaultPassword);

    $fechaNacimiento = new \DateTime($validatedData['fecha_nacimiento']);
    $hoy = new \DateTime();
    $edad = $hoy->diff($fechaNacimiento)->y;
    $isMayorEdad = $edad >= 18;

    Log::info('Creando usuario', ['email_generado' => $generatedEmail]);
    $user = User::create([
        'nombre' => $validatedData['nombre'],
        'apellidos' => $validatedData['apellidos'],
        'email' => $generatedEmail, // Guardamos el email generado
        'password' => $hashedPassword,
        'fecha_nacimiento' => $validatedData['fecha_nacimiento'],
        'numero_telefono' => $validatedData['numero_telefono'],
        'dni' => $validatedData['dni'],
        'ciudad_id' => $validatedData['ciudad'],
        'codigo_postal' => $validatedData['codigo_postal'],
        'mayor_edad' => $isMayorEdad,
        'id_descuento' => 1,
        'tipo_usuario' => $validatedData['tipo_usuario'],
    ]);
    Log::info('Usuario creado', ['user_id' => $user->id]);

    if ($validatedData['tipo_usuario'] == 1) {
        $codigoAdmin = '';
        $isUniqueCode = false;
        do {
            $letra1 = Str::random(1, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
            $numeros = sprintf('%02d', rand(0, 99));
            $letra2 = Str::random(1, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
            $codigoAdmin = $letra1 . $numeros . $letra2;
            $isUniqueCode = !Administrator::where('codigo_administrador', $codigoAdmin)->exists();
        } while (!$isUniqueCode);
        $codigoAdminGenerado = Str::upper($codigoAdmin);

        $nombreUserAdmin = '';
        $isUniqueAdminName = false;
        $nombreBaseAdmin = Str::slug($validatedData['nombre'], '_');
        do {
            $randomSuffix = rand(100, 999);
            $nombreUserAdmin = $nombreBaseAdmin . '_Cosmos' . $randomSuffix;
            $isUniqueAdminName = !Administrator::where('nombre_user_admin', Str::ucfirst($nombreUserAdmin))->exists();
        } while (!$isUniqueAdminName);

        Log::info('Creando administrador', ['email_admin' => $generatedEmail, 'codigo' => $codigoAdminGenerado, 'nombre_admin' => $nombreUserAdmin]);
        Administrator::create([
            'email' => $generatedEmail, // Guardamos el email generado
            'codigo_administrador' => $codigoAdminGenerado,
            'nombre_user_admin' => Str::ucfirst($nombreUserAdmin),
        ]);
        Log::info('Administrador creado');

        // Enviar correo a Administrador usando el email del input
        Log::info('Enviando correo a administrador', ['email' => $emailParaCorreo]);
        Mail::to($emailParaCorreo)->send(new CredencialesEmpleado($validatedData['nombre'], $generatedEmail, $defaultPassword, true, Str::ucfirst($nombreUserAdmin), $codigoAdminGenerado));
        Log::info('Correo a administrador enviado');

    } else if ($user->tipo_usuario == 2) {
        // Enviar correo a Empleado usando el email del input
        Log::info('Enviando correo a empleado', ['email' => $emailParaCorreo]);
        Mail::to($emailParaCorreo)->send(new CredencialesEmpleado($validatedData['nombre'], $generatedEmail, $defaultPassword, $esAdmin));
        Log::info('Correo a empleado enviado');
    }

    Log::info('Respondiendo con JSON');
    return response()->json([
        'message' => 'Empleado creado exitosamente y credenciales enviadas por correo.',
    ], 201);
}

    public function gestionarNominasIndex(Request $request)
    {
        // Obtener todos los usuarios que pueden tener nóminas (ej. tipo_usuario 1=Admin, 2=Empleado)
        // Podrías filtrar más si los administradores no tienen nóminas o solo quieres empleados.
        $usuariosConNominas = User::whereIn('tipo_usuario', [1, 2])
                                ->orderBy('apellidos')->orderBy('nombre')
                                ->get();

        $selectedUserId = $request->input('user_id');
        $nominas = collect(); // Colección vacía por defecto
        $selectedUser = null;

        // Variables para filtros de nóminas (mes, año, fechas)
        $filterMes = $request->input('mes');
        $filterAnio = $request->input('anio');
        $filterFechaInicio = $request->input('fecha_inicio');
        $filterFechaFin = $request->input('fecha_fin');

        if ($selectedUserId) {
            $selectedUser = User::with('ciudad')->find($selectedUserId); // Carga la ciudad si es necesario para la vista
            if ($selectedUser) {
                $query = $selectedUser->nominas(); // Usa la relación definida en User

                // Aplicar filtros de nóminas si están presentes
                if ($filterMes) {
                    $query->where('mes', $filterMes);
                }
                if ($filterAnio) {
                    $query->where('anio', $filterAnio);
                }
                if ($filterFechaInicio) {
                    $query->whereDate('generacion_fecha', '>=', $filterFechaInicio);
                }
                if ($filterFechaFin) {
                    $query->whereDate('generacion_fecha', '<=', $filterFechaFin);
                }

                $nominas = $query->orderBy('anio', 'desc')
                                ->orderBy('mes', 'desc')
                                ->paginate(15)
                                ->appends($request->except('page')); // Mantener todos los filtros en la paginación
            }
        }

        return view('administrador.nominas.gestion', compact(
            'usuariosConNominas',
            'selectedUserId',
            'selectedUser',
            'nominas',
            'filterMes',
            'filterAnio',
            'filterFechaInicio',
            'filterFechaFin'
        ));
    }

    /**
     * Genera y muestra (stream) el PDF de una nómina específica para el administrador.
     * (El administrador puede ver la nómina de cualquier empleado).
     */
    public function generarPdfNominaAdmin($idNomina) // Nombre diferente al del NominaEmpleadoController
    {
        $nomina = NominaEmpleados::with('empleado.city')->findOrFail($idNomina); // O NominaEmpleados
        $empleado = $nomina->empleado;

        // No hay restricción de $currentUser->id === $empleado->id aquí, el admin puede ver todas.

        $empresa = (object) [
            'nombre_legal' => config('company.name', 'Cosmos Cinema S.L.'),
            'cif' => config('company.cif', 'B12345678'),
            'direccion' => config('company.address', 'Calle Principal 1, 28001 Madrid'),
            'representante_legal' => config('company.legal_representative', 'D. Gerente Ejemplo')
        ];

        $pdf = Pdf::loadView('pdf.nomina', compact('nomina', 'empleado', 'empresa'));

        $periodoParaNombreArchivo = str_replace('/', '-', $nomina->periodoCompleto);
        $nombreArchivo = 'nomina-' . $periodoParaNombreArchivo . '-' . $empleado->dni . '.pdf';

        return $pdf->stream($nombreArchivo);
    }

    /**
     * Genera y descarga el PDF de una nómina específica para el administrador.
     */
    public function downloadNominaPdfAdmin($idNomina) // Nombre diferente
    {
        $nomina = NominaEmpleados::with('empleado.city')->findOrFail($idNomina); // O NominaEmpleados
        $empleado = $nomina->empleado;

        $empresa = (object) [
            'nombre_legal' => config('company.name', 'Cosmos Cinema S.L.'),
            'cif' => config('company.cif', 'B12345678'),
            'direccion' => config('company.address', 'Calle Principal 1, 28001 Madrid'),
            'representante_legal' => config('company.legal_representative', 'D. Gerente Ejemplo')
        ];

        $pdf = Pdf::loadView('pdf.nomina', compact('nomina', 'empleado', 'empresa'));

        $periodoParaNombreArchivo = str_replace('/', '-', $nomina->periodoCompleto);
        $nombreArchivo = 'nomina-' . $periodoParaNombreArchivo . '-' . $empleado->dni . '.pdf';

        return $pdf->download($nombreArchivo);
    }


    public function index()
    {
        $generos_tmdb = PeticionPeliculasController::peticion_generos();
        $ciudades = Ciudad::orderBy('nombre')->get();
        return view('administrador.dashboard', compact('generos_tmdb', 'ciudades'));
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/administrador')->with('success', '¡Sesión de administrador cerrada correctamente!');
    }
}
