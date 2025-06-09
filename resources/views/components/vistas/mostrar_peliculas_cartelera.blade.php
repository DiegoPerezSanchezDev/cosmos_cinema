@props(['peliculas'])
<script>
    var peliculas = @json($peliculas);
</script>

<div class='cartelera-box'>
    <div class='cartelera-titulo'>
        <h3>Cartelera</h3>
    </div>
    <div class='separador'></div>
    <div class='cartelera'>

        @if(!empty($peliculas))
            @foreach($peliculas as $pelicula_id => $pelicula)
                <div class='cartel_pelicula' id='{{ $pelicula["id"] }}' onclick="mostrar_detalle('{{ $pelicula["id"] }}')">
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
        @else
            <div>
                 No hay películas disponibles en este momento
            </div>
        @endif
       
    </div>
</div>