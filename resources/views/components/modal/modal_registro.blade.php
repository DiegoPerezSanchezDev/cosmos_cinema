<div class="modal hidden" id="modalRegistro">
    <div class="form-container">
        <button class="close-button" id="cerrarRegistro">×</button>
        <div class="logo-container">
            <img src="{{ asset('images/logoCosmosCinema.webp') }}" alt="Cosmos Cinema Logo" class="logo">
        </div>

        {{-- Opción de Registro con Google --}}
        <div style="text-align: center; margin-bottom: 25px;">
        <a href="{{ route('login.google') }}" class="btn-google"> {{-- login.google también maneja el registro si el usuario no existe --}}
                <svg class="google-logo-svg" width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.64,9.20455c0-.63864-.05682-1.25227-.16818-1.84091H9.18182V11.1h4.75c-.20455,1.33864-.81818,2.47727-1.75,3.23864v2.14772h2.75455C16.96364,14.71591,17.64,12.21591,17.64,9.20455Z" fill="#4285F4"/>
                    <path d="M9.18182,18c2.43182,0,4.47727-.80682,5.97727-2.18182l-2.75455-2.14772c-.80682,.54545-1.84091,.875-3.22273,.875-2.47727,0-4.58182-1.66818-5.32955-3.90909H1.09091v2.21591C2.58182,16.26136,5.625,18,9.18182,18Z" fill="#34A853"/>
                    <path d="M3.85227,10.73864c-.20455-.61364-.31818-1.26136-.31818-1.93182s.11364-1.31818,.31818-1.93182V4.65909H1.09091C.386364,6.04545,0,7.54545,0,9.18182s.386364,3.13636,1.09091,4.52273Z" fill="#FBBC05"/>
                    <path d="M9.18182,3.54545c1.32955,0,2.51136,.46591,3.45455,1.36364l2.44318-2.44318C13.64773,.988636,11.60227,0,9.18182,0,5.625,0,2.58182,1.73864,1.09091,4.65909l2.76136,2.21591C4.6,4.61364,6.70455,3.54545,9.18182,3.54545Z" fill="#EA4335"/>
                </svg>
                <span>Registrarse con Google</span>
            </a>
            <p style="margin-top: 20px; margin-bottom: 5px; color: white;">o regístrate con tu email:</p>
            <hr style="margin-bottom: 20px; border-top: 1px solid #eee;">
        </div>

        <form method="POST" action="{{ route('registro')}}">
            @csrf

            {{-- PASO 1: Email y Contraseña --}}
            <div id="step1Registro" class="form-step active">
                <div class="form-row">
                    @if ($errors->has('email')) {{-- Errores del backend --}}
                    <div class="error-message backend-error">{{ $errors->first('email') }}</div>
                    @endif
                    <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required class="input" autocomplete="email">
                    <span class="client-side-field-error" style="color: red; display: none; margin-bottom: 10px; font-size: 0.9em;"></span>
                </div>

                <div class="form-row">
                    @if ($errors->has('email_confirmation'))
                    <div class="error-message backend-error">{{ $errors->first('email_confirmation') }}</div>
                    @endif
                    <input type="email" name="email_confirmation" placeholder="Confirmar tu email" value="{{ old('email_confirmation') }}" required class="input" autocomplete="email">
                    <span class="client-side-field-error" style="color: red; display: none; margin-bottom: 10px; font-size: 0.9em;"></span>
                </div>

                <div class="form-row">
                    @if ($errors->has('password'))
                    <div class="error-message backend-error">{{ $errors->first('password') }}</div>
                    @endif
                    <div class="password-input-container">
                        <input type="password" name="password" placeholder="Contraseña" required class="input" autocomplete="new-password">
                        <span class="toggle-password" style="cursor: pointer; position: absolute; right: 10px; top: 38%; transform: translateY(-50%);">👁️</span>
                    </div>
                    <span class="client-side-field-error" style="color: red; display: none; margin-bottom: 10px; font-size: 0.9em;"></span>
                    {{-- Aquí podrías añadir una pequeña guía de fortaleza de contraseña con JS si quieres --}}
                </div>

                <div class="form-row">
                    @if ($errors->has('password_confirmation'))
                    <div class="error-message backend-error">{{ $errors->first('password_confirmation') }}</div>
                    @endif
                    <div class="password-input-container">
                        <input type="password" name="password_confirmation" placeholder="Confirmar contraseña" required class="input" autocomplete="new-password">
                        <span class="toggle-password" style="cursor: pointer; position: absolute; right: 10px; top: 38%; transform: translateY(-50%);">👁️</span>
                    </div>
                    <span class="client-side-field-error" style="color: red; display: none; margin-bottom: 10px; font-size: 0.9em;"></span>
                </div>

                {{-- reCAPTCHA (si es para el registro manual completo) --}}
                {{-- Si este formulario solo pide email/pass y luego hay más pasos, el reCAPTCHA iría en el último paso. --}}
                {{-- Si este es el ÚNICO paso del registro manual antes de enviar, ponlo aquí. --}}
                <div class="form-row" style="display: flex; justify-content: center; flex-direction: column; margin-top:15px; margin-bottom: 15px;">
                    <div class="g-recaptcha" id="recaptcha-registro-container" data-sitekey="{{ env('RECAPTCHA_SITE_KEY_REGISTRO') }}"></div>
                    @error('recaptcha_registro')
                        <div class="error-messages" style="width:100%; text-align:center; margin-top: 5%;">{{ $message }}</div>
                    @enderror
                </div>


                <div class="client-side-errors" style="display: none; color: red; margin-top: 10px;">
            <ul></ul>
        </div>

        <div class="button-group" style="margin-top: 20px;">
            <button type="button" class="btn back-button" id="cancelarRegistroBtn">Cancelar</button>
            <button type="submit" class="btn" id="submitRegistroManualBtn">Registrarse</button>
        </div>
            </div>


    {{-- Mostrar errores generales del backend (del error bag 'registro') --}}
    @if ($errors->hasBag('registro') && $errors->getBag('registro')->any())
    <div class="error-messages backend-error" style="margin-top: 15px; text-align: left;">
        <ul style="padding-left: 20px;">
            @foreach ($errors->getBag('registro')->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    {{-- Para errores generales de AJAX si el form se envía con JS --}}
    <div id="generalRegistroError" class="error-message" style="display: none; margin-top: 15px; text-align: center;"></div>


    </form>
</div>
</div>