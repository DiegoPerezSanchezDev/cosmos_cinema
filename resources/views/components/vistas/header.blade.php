{{-- Menú Hamburguesa --}}
<button class="hamburger-button" id="hamburgerButton" aria-label="Open menu" aria-expanded="false" aria-controls="headerNavLinks">
    <span class="hamburger-button__line"></span>
    <span class="hamburger-button__line"></span>
    <span class="hamburger-button__line"></span>
</button>

{{-- Menú normal --}}
<div class="header-buttons" id="headerNavLinks" style="display: flex; align-items: center;">
    @if (session('success'))
    <div id="flash-message" class="success-message">
        {{ session('success') }}
    </div>
    @endif
    <button id="mostrarMenus"> <a class="botones" href="#seccionMenus">CARTA COSMOS</a></button>
    @if(Auth::check())
    <button id="miCuenta"><a class="botones" href="#">MI CUENTA</a></button>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: inline;">
        @csrf
        <button type="submit" id="logout">
            <a class="botones">LOGOUT</a>
        </button>
    </form>
    @else
    <button id="mostrarRegistro"><a class="botones">ÚNETE A COSMOS</a></button>
    <button id="mostrarLogin"><a class="botones">INICIAR SESIÓN</a></button>
    @endif
</div>

<div class="logo-container">
    <a href="{{ route('principal') }}" alt="Cosmos Cinema">
        <img src="{{ asset('images/logoCosmosCinema.webp') }}" alt="Cosmos Cinema Logo" class="cinema-logo">
    </a>
</div>

