@props(['peliculas_estreno'])
<script>
    var peliculas_estreno = @json($peliculas_estreno);
</script>

@if(!empty($peliculas_estreno))
<div class='cartelera-box'>
    <div class='cartelera-titulo'>
        <h3>Próximamente en Cosmos Cinema</h3>
    </div>
    <div class='separador'></div>
    <div class='cartelera'>
        
        @foreach($peliculas_estreno as $pelicula_id => $pelicula)
            <div class='cartel_pelicula' id='{{ $pelicula["id"] }}' onclick="mostrar_detalle_estreno('{{ $pelicula["id"] }}')">
                <div class='imagen_pelicula_cartel'>
                    @if (isset($pelicula['poster_url']))
                        <img class='movie_poster' src="{{ $pelicula['poster_url'] }}" loading="lazy" alt="{{ $pelicula['titulo'] }}">
                    @else
                        <p>Póster no disponible</p>
                    @endif
                </div>
                <div class='titulo_pelicula_cartel'>
                    <h4>{{ $pelicula["titulo"] }}</h4>
                </div>
            </div>
        @endforeach
            
    </div>
</div>
@endif