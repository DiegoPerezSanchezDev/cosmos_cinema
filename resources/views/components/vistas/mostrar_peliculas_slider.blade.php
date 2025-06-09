<script>
    try {
        window.heroSliderPeliculasData = @json($peliculas);
    } catch (e) {
        window.heroSliderPeliculasData = [];    
    }
</script>

@if(count($peliculas) > 0)
<div class="hero-slider-container">
    <div class="swiper hero-movie-swiper">
        <div class="swiper-wrapper">
            @foreach($peliculas as $pelicula)
            @php
            $tituloPelicula = $pelicula['titulo'] ?? "Sin Título";
            $peliculaId = $pelicula['id'] ?? null;
            $imageUrl = $pelicula['backdrop_url'];
            $imagePoster = $pelicula['poster_url'];
            $loopIndex = $loop->index;
            @endphp

            <div class="swiper-slide hero-slide" data-slide-index="{{ $loopIndex }}" onclick="mostrar_detalle('{{ $peliculaId }}')">
                <img src="{{ $imageUrl }}" alt="Imagen de fondo de {{ $tituloPelicula }}" class="hero-slide-background-image">
                <img src="{{ $imagePoster }}" alt="Póster de {{ $tituloPelicula }}" class="hero-slide-poster-image">
                <div class="hero-slide-overlay-gradient"></div>
                <div class="hero-slide-content">
                    <h2 class="hero-slide-title">{{ $tituloPelicula }}</h2>
                </div>
            </div>
            @endforeach
        </div>
        <div class="swiper-pagination hero-swiper-pagination"></div>
        <div class="swiper-button-prev hero-swiper-button-prev">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z" />
            </svg>
        </div>
        <div class="swiper-button-next hero-swiper-button-next">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z" />
            </svg>
        </div>
    </div>
</div>
@endif
