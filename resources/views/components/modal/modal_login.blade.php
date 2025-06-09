<div class="modal hidden" id="modalLogin">
    <div class="form-container">
        <button class="close-button" id="cerrarLogin">&times;</button>
        <div class="logo-container">
            <img src="{{ asset('images/logoCosmosCinema.webp') }}" alt="Cosmos Cinema Logo" class="logo">
        </div>
        <form method="POST" id="login-form" action="{{ route('login') }}" novalidate>
            @csrf

            <div class="form-row">
                <input class="input" type="email" name="login_email" id='login_email'
                    placeholder="Email" value="{{ old('login_email') }}" required>
                <p class="client-side-field-error"></p>
                @error('login_email')
                <p class="error-text">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-row">
                <div class="password-input-container">
                    <input class="input" type="password" name="login_password" id='login_password'
                        placeholder="**********" required>
                    <span class="toggle-password" style="cursor: pointer; position: absolute; right: 10px; top: 38%; transform: translateY(-50%);">👁️</span>
                </div>
                <p class="client-side-field-error"></p>
                @error('login_password')
                <p class="error-text">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-row">
                <label>
                    <input type="checkbox" name="remember"> Recordarme
                </label>
            </div>

            <div style="text-align: center; margin-top: 20px; margin-bottom: 20px;">
                <p style="margin-bottom: 10px;">O inicia sesión con:</p>
                <a href="{{ route('login.google') }}" class="btn btn-google" style="display: inline-flex; align-items: center; border: white solid 1px; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-size: 0.95em; box-shadow: 0 2px 4px 0 rgba(0,0,0,0.25);">
                    {{-- Puedes usar un SVG o una imagen para el logo de Google --}}
                    <svg class="google-logo-svg" width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.64,9.20455c0-.63864-.05682-1.25227-.16818-1.84091H9.18182V11.1h4.75c-.20455,1.33864-.81818,2.47727-1.75,3.23864v2.14772h2.75455C16.96364,14.71591,17.64,12.21591,17.64,9.20455Z" fill="#4285F4" />
                        <path d="M9.18182,18c2.43182,0,4.47727-.80682,5.97727-2.18182l-2.75455-2.14772c-.80682,.54545-1.84091,.875-3.22273,.875-2.47727,0-4.58182-1.66818-5.32955-3.90909H1.09091v2.21591C2.58182,16.26136,5.625,18,9.18182,18Z" fill="#34A853" />
                        <path d="M3.85227,10.73864c-.20455-.61364-.31818-1.26136-.31818-1.93182s.11364-1.31818,.31818-1.93182V4.65909H1.09091C.386364,6.04545,0,7.54545,0,9.18182s.386364,3.13636,1.09091,4.52273Z" fill="#FBBC05" />
                        <path d="M9.18182,3.54545c1.32955,0,2.51136,.46591,3.45455,1.36364l2.44318-2.44318C13.64773,.988636,11.60227,0,9.18182,0,5.625,0,2.58182,1.73864,1.09091,4.65909l2.76136,2.21591C4.6,4.61364,6.70455,3.54545,9.18182,3.54545Z" fill="#EA4335" />
                    </svg>
                    Iniciar Sesión con Google
                </a>
            </div>

                <div class="form-row" style="display: flex; justify-content: center; flex-direction: column; margin-top:15px; margin-bottom: 15px;">
                    <div class="g-recaptcha" id="recaptcha-login-container" data-sitekey="{{ env('RECAPTCHA_SITE_KEY_LOGIN') }}"></div>
                    @error('recaptcha_login')
                        <div class="error-messages" style="width:100%; text-align:center; margin-top: 5%;">{{ $message }}</div>
                    @enderror
                </div>

            <div class="client-side-errors1" style="color: red; display: none; margin-bottom: 10px;"><ul></ul></div>


            <div class="button-group">
                <button type="button" class="btn back-button" id="volverLoginModal">Volver</button>
                <button type="submit" class="btn">Login</button>
            </div>

        </form>
    </div>
</div>