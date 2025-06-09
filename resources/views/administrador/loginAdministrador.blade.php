<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Cosmos Cinema</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        window.appConfig = {
            checkEmailRoleUrl: "{{ route('check.email.role') }}"
        };
    </script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @vite(['resources/css/adminLogin.css',
        "resources/js/loginAdmin.js"])
</head>
<body>
<div class="login-container">
    <h2>Empleados Cosmos</h2>
    <form action="{{ route('admin.login.submit') }}" method="POST" id="adminLoginForm" class="login-form">
        @csrf

        @if (session('error'))
            <div class="error-message general-error">{{ session('error') }}</div>
        @endif

        <input type="email" name="email"  id="email-input" placeholder="Email" required autofocus value="{{ old('email') }}">

        <div class="input-password-container">
            <input type="password" name="password" placeholder="Contraseña" required>
            <span class="toggle-password">
                👁️
            </span>
        </div>

        <div id="admin-code-container">
            <input type="text" name="codigo_administrador" id="codigo_administrador" placeholder="Código de Administrador" required value="{{ old('codigo_administrador') }}">
        </div>

        <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>

        @if ($errors->any())
            <div class="validation-errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        @if ($error != $errors->first('codigo_administrador'))
                        <li>{{ $error }}</li>
                        @endif
                    @endforeach

                    @if($errors->has('codigo_administrador'))
                        <li>{{ $errors->first('codigo_administrador') }}</li>
                    @endif
                </ul>
            </div>
        @endif

        <button type="submit">Login</button>
    </form>
</div>

</body>
</html>