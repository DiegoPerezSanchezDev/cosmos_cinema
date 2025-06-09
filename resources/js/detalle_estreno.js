// Función para mostrar el modal de detalle de estreno
window.mostrar_detalle_estreno = function (pelicula_estreno_id) {

    const modal_detalle = document.getElementById('modal_detalle');

    const pelicula_estreno = peliculas_estreno[pelicula_estreno_id];
    const imagen_box = document.getElementById('detalle_imagen_box');
    const titulo_element = document.getElementById('detalle_titulo');
    const estreno_element = document.getElementById('detalle_estreno');
    const duracion_element = document.getElementById('detalle_duracion');
    const sinopsis_element = document.getElementById('sinopsis');
    const comprar_btn = document.getElementById('detalle_comprar_btn');
    const detalle_proximamente_btn = document.getElementById('detalle_proximamente_btn');

    // Texto de carga de imagen
    imagen_box.innerHTML = '<p>Cargando imagen...</p>';

    // Si el aspect ratio es menor de 1.5, la imagen del detalle pasa de backdrop a poster
    // Recuperar aspect ratio
    const viewport_width = window.innerWidth;
    const viewport_height = window.innerHeight;
    const aspect_ratio = viewport_width / viewport_height;

    const aspect_ratio_limite = 1.3;
    let imagen_url;
    if (aspect_ratio < aspect_ratio_limite) {
        imagen_url = pelicula_estreno.poster_url;
    } else {
        imagen_url = pelicula_estreno.backdrop_url;
    }

    // Imagen
    imagen_box.innerHTML = `<img class='detalle_imagen' src="${imagen_url}" loading="lazy" alt="${pelicula_estreno.titulo}">`;

    // Textos
    titulo_element.textContent = pelicula_estreno.titulo;
    estreno_element.textContent = `Fecha de Estreno: ${pelicula_estreno.fecha_estreno}`;
    duracion_element.textContent = `Duración: ${pelicula_estreno.duracion} min`;
    sinopsis_element.textContent = pelicula_estreno.sinopsis;

    if (modal_detalle.classList.contains('hidden')) {
        modal_detalle.classList.remove('hidden');
        modal_detalle.classList.add('visible');
        document.body.classList.add('modal_abierto');
    }

    if (detalle_proximamente_btn.classList.contains('hidden')) {
        detalle_proximamente_btn.classList.remove('hidden');
        comprar_btn.classList.add('hidden');
    }

    // Funcionalidad de botón COMPRAR ENTRADA
    if (detalle_proximamente_btn) {
        // Si ya tiene un event listener, se elimina
        if (detalle_proximamente_btn.getAttribute('listener') == 'true') {
            detalle_proximamente_btn.removeEventListener('click', cerrar_modal_proximamente);
            detalle_proximamente_btn.removeAttribute('listener');
        }

        // Ocultar modal mostrar_detalle
        detalle_proximamente_btn.addEventListener('click', cerrar_modal_proximamente);
        detalle_proximamente_btn.setAttribute('listener', 'true');
    }

};



function cerrar_modal_proximamente() {
    if (modal_detalle && modal_detalle.classList.contains('visible')) {

        // Ocultar mostrar_detalle
        modal_detalle.classList.remove('visible');
        modal_detalle.classList.add('hidden');
        document.body.classList.remove('modal_abierto');
    }
}