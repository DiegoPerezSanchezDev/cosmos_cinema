// Función para mostrar el modal de detalle
window.mostrar_detalle = function (peliculaId) {

    const modal_detalle = document.getElementById('modal_detalle');

    const pelicula = peliculas[peliculaId];
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
        imagen_url = pelicula.poster_url;
    } else {
        imagen_url = pelicula.backdrop_url;
    }

    // Imagen
    imagen_box.innerHTML = `<img class='detalle_imagen' src="${imagen_url}" loading="lazy" alt="${pelicula.titulo}">`;

    // Textos
    titulo_element.textContent = pelicula.titulo;
    estreno_element.textContent = `Fecha de Estreno: ${pelicula.fecha_estreno}`;
    duracion_element.textContent = `Duración: ${pelicula.duracion} min`;
    sinopsis_element.textContent = pelicula.sinopsis;

    if (modal_detalle.classList.contains('hidden')) {
        modal_detalle.classList.remove('hidden');
        modal_detalle.classList.add('visible');
        document.body.classList.add('modal_abierto');
    }

    if (comprar_btn.classList.contains('hidden')) {
        comprar_btn.classList.remove('hidden');
        detalle_proximamente_btn.classList.add('hidden');
    }

    // Funcionalidad de botón COMPRAR ENTRADA
    if (comprar_btn && seccionCompra) {
        // Si ya tiene un event listener, se elimina
        if (comprar_btn.getAttribute('listener') == 'true') {
            comprar_btn.removeEventListener('click', elegir_pelicula);
            comprar_btn.removeAttribute('listener');
        }

        // Se crea un nuevo event listener, para evitar que se acumulen Peliculas IDs en el mismo botón
        comprar_btn.addEventListener('click', elegir_pelicula);
        comprar_btn.setAttribute('listener', 'true');
        comprar_btn.dataset.peliculaId = peliculaId;
    }

};





function elegir_pelicula() {
    // Recuperar elementos necesarios
    const comprar_btn = document.getElementById('detalle_comprar_btn');
    const peliculaId = comprar_btn.dataset.peliculaId;
    const modal_detalle = document.getElementById('modal_detalle');

    // Oculta el Modal
    modal_detalle.classList.remove('visible');
    modal_detalle.classList.add('hidden');
    document.body.classList.remove('modal_abierto');


    // Hace visible el div de elegir sesión
    if (seccionCompra.classList.contains('hidden')) {
        seccionCompra.classList.remove('hidden');
        seccionCompra.classList.add('visible');
        document.body.classList.remove('modal_abierto');
    }

    // Lleva la vista al nuevo div creado
    seccionCompra.scrollIntoView({
        behavior: 'smooth'
    });

    // Se llama a la función que genera dinámicamente la sección de selección de asientos
    mostrar_seleccion_asientos(peliculaId);
}


// Para que la ventana se cierre al presionar Escape
if (modal_detalle) {
    document.addEventListener('keydown', function (event) {
        if ((event.key === 'Escape' || event.keyCode === 27) && modal_detalle.classList.contains('visible')) {

            event.preventDefault();

            // Hide the modal by reversing the class logic from mostrar_detalle
            modal_detalle.classList.remove('visible');
            modal_detalle.classList.add('hidden');
            document.body.classList.remove('modal_abierto');
        }
    });
}


// Para que la ventana se cierre al clicar fuera del modal
if (modal_detalle) {
    modal_detalle.addEventListener('click', function (event) {
        if (event.target === modal_detalle && modal_detalle.classList.contains('visible')) {
            modal_detalle.classList.remove('visible');
            modal_detalle.classList.add('hidden');
            document.body.classList.remove('modal_abierto');
        }
    });
}


// Función para mostrar la sección Compra (solo se activará al presionar COMPRAR ENTRADA)
// Realiza una petición AJAX al controlador X para recuperar las sesiones de la película seleccionada

function mostrar_seleccion_asientos(peliculaId) {
    const seccionCompra = document.getElementById('seccionCompra');
    const seccion_sesiones = seccionCompra ? seccionCompra.querySelector('.seccion_sesiones') : null;
    let primera = true;

    // Comprobar que el div existe
    if (!seccion_sesiones) {
        console.error("Div '.seccion_sesiones' no encontrado dentro de '#seccionCompra'.");
        return;
    }

    // Mensaje de carga (por si tarda en cargar la petición)
    seccion_sesiones.innerHTML = "<p>Cargando sesiones...</p>";

    // Ruta de controlador
    const sesiones_url = `/recuperar_sesiones/id_pelicula=${peliculaId}`; // Example using peliculaId in the path

    fetch(sesiones_url)
        .then(response => {
            // Comprobar si la respuesta fue exitosa
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return response.json();
        })
        .then(sesiones => {

            // Comprobar si sesiones tiene datos disponibles
            if (!sesiones || sesiones.length === 0) {
                seccion_sesiones.innerHTML = "<p>No hay sesiones disponibles para esta película en los próximos días.</p>";
                return;
            }

            let fila_sesion = '';

            const sesiones_por_dias = {};

            // Ordenar y filtrar las sesiones por días (ahora cada día tendrá varias horas)
            sesiones.forEach(sesion => {
                const fecha = sesion.fecha.fecha;

                if (!sesiones_por_dias[fecha]) {
                    sesiones_por_dias[fecha] = [];
                }

                sesiones_por_dias[fecha].push(sesion);
            });

            // Recorrer cada sesión y generar el HTML con los datos recogidos
            Object.entries(sesiones_por_dias).forEach(([dia, sesion]) => {

                const primera_sesion = sesion[0];
                const dia_semana = primera_sesion.dia_semana;
                const dia_calendario = dia;

                // Formatear día de la semana (Domingo -> Dom)
                const dia_semana_format = dia_semana.substring(0, 3); // "Dom"

                // Formatear el día del calendario (YYYY-MM-DD -> DD / MM)
                const dia_array = dia_calendario.split('-');
                const dia_calendario_format = `${dia_array[2]} / ${dia_array[1]}`;
                const dia_div = `
                             <div class="day-info">
                                 <div class="day-label">${dia_semana_format}</div>
                                 <div class="date-label">${dia_calendario_format}</div>
                             </div>
                         `;

                // Generar los botones de las horas
                let horas_botones = "";

                // Se recorren las horas de cada día y se genera un botón por cada hora
                // El id del botón será el id de la sesión (para luegor recuperar los asientos)

                sesion.map(sesion_hora => {
                    const hora = sesion_hora.hora.hora;
                    horas_botones += `
                            <div class="session-time" id="${sesion_hora.id}">
                                ${hora}
                            </div>
                        `;

                    // Los asientos de la primera hora se mostrarán por defecto
                    if (primera) {
                        generar_asientos(sesion_hora.id);
                        primera = false;
                    }
                });

                // Generar div de horas
                const horas_div = `
                          <div class="times-list">
                              ${horas_botones}
                          </div>
                     `;

                // Generar el div de sesiones
                fila_sesion += `
                         <div class="session-row">
                             ${dia_div}
                             ${horas_div}
                         </div>
                     `;
            });


            // Rellenar el div de la página con el div de las sesiones generado
            seccion_sesiones.innerHTML = fila_sesion;

            // Recoger todos los botones y añadirles un EventListener para cuando sean clicados
            const horas_sesiones = seccion_sesiones.querySelectorAll('.session-time');
            horas_sesiones.forEach(hora_sesion => {
                hora_sesion.addEventListener('click', function () {
                    // Cuando se clique en el botón se llamará a la función para generar asientos
                    const id_sesion = this.id;
                    generar_asientos(id_sesion);
                });
            });

        })
        .catch(error => {
            // Detectar errores
            console.error('Error al obtener los datos de las sesiones:', error);

            if (seccion_sesiones) {
                seccion_sesiones.innerHTML = `<p>Error al cargar las sesiones. Por favor, inténtelo de nuevo</p>`;
            }
        });

}



function generar_asientos(id_sesion) {

    const sesion_asientos = `/recuperar_asientos/id_sesion=${id_sesion}`; // Example using peliculaId in the path
    const seccion_asientos = document.getElementById('seccion_asientos');
    const superior_izquierda = document.getElementById('superior_izquierda');

    // Mensaje de carga (por si tarda en cargar la petición)
    limpiar_datos_asientos();
    superior_izquierda.innerHTML = "<p>Cargando asientos...</p>";

    fetch(sesion_asientos)
        .then(response => {
            // Comprobar si la respuesta fue exitosa
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return response.json();
        })
        .then(datos_sesion => {
            // Div container
            let container;
            if (container = document.getElementById('container')) {
                container.innerHTML = "";
            } else {
                container = document.createElement('div');
                container.classList.add('container');
                container.id = 'container';
            }

            // Eliminar asientos seleccionados (si existían)
            const container_asientos_selec = document.getElementById('container_asientos_selec');
            if (container_asientos_selec) {
                container_asientos_selec.remove();
            }

            // Eliminar botón CONFIRMAR COMPRA y precio total (si existía)
            const listado_asientos_abajo = document.getElementById('listado_asientos_abajo');
            if (listado_asientos_abajo) {
                listado_asientos_abajo.remove();
            }

            superior_izquierda.innerHTML = "";

            // Generar los datos de la sesión seleccionada
            generar_datos_sesion(datos_sesion);

            // Generar géneros
            generar_generos_sesion(datos_sesion);

            // Generar leyenda de asientos
            generar_leyenda();

            seccion_asientos.appendChild(container);

            const asientos = datos_sesion.asientos;

            // Comprobar que hemos recuperado correctamente los asientos
            if (!asientos || asientos.length === 0) {
                console.warn("No hay datos de asientos para mostrar.");
                container.innerHTML = '<p>No hay asientos disponibles para esta sesión.</p>';
                return;
            }

            const asientosMap = new Map();
            let numFilas = 0;
            let numColumnas = 0;

            // Crear mapa de asientos
            asientos.forEach(asiento => {
                // Ignorar asientos con coordenadas inválidas
                if (asiento.fila > 0 && asiento.columna > 0) {
                    // Crear una clave única para cada asiento basada en su fila y columna
                    const key = `${asiento.fila}-${asiento.columna}`;
                    asientosMap.set(key, asiento);

                    // Actualizar las dimensiones máximas encontradas
                    if (asiento.fila > numFilas) numFilas = asiento.fila;
                    if (asiento.columna > numColumnas) numColumnas = asiento.columna;
                }
            });

            container.innerHTML = '';
            const screen = document.createElement('div');
            screen.classList.add('screen');
            container.appendChild(screen);

            const fragment = document.createDocumentFragment();

            // Generar filas y columnas, dejando espacios vacíos en los asientos que no tengan esos valores
            // Filas
            for (let i = 1; i <= numFilas; i++) {
                const filaDiv = document.createElement('div');
                filaDiv.classList.add('row');

                // Columnas
                for (let j = 1; j <= numColumnas; j++) {
                    const key = `${i}-${j}`;
                    const asientoData = asientosMap.get(key); // Búsqueda O(1) en el Map

                    const elementoCelda = document.createElement('div');

                    // Generar asiento
                    if (asientoData) {
                        elementoCelda.classList.add('seat');
                        // Guardar información útil en atributos data-*
                        elementoCelda.id = asientoData.id_asiento;
                        elementoCelda.dataset.fila = i;
                        elementoCelda.dataset.columna = j;
                        elementoCelda.dataset.id_sesion = id_sesion;
                        elementoCelda.dataset.id = asientoData.id_asiento;
                        elementoCelda.estado = asientoData.estado;

                        // Aplicar clase según el estado del asiento
                        switch (asientoData.estado) {
                            // Disponible
                            case 1:
                                elementoCelda.classList.add('available');
                                break;
                            // Ocupado
                            case 2:
                                elementoCelda.classList.add('occupied');
                                break;
                            // Desconocido
                            default:
                                break;
                        }

                        // Añadir EventListener a cada asiento
                        elementoCelda.addEventListener('click', function () {
                            // Cuando se clique en el asiento se llamará a la función para seleccionar o deseleccionar asiento
                            const id_asiento = asientoData.id_asiento;
                            seleccionar_asiento(datos_sesion, id_asiento);
                        });

                    } else {
                        // Es un hueco / pasillo
                        elementoCelda.classList.add('gap');
                    }

                    // Añadir la celda a la fila
                    filaDiv.appendChild(elementoCelda);
                }
                // Añadir la fila al fragmento
                fragment.appendChild(filaDiv);
            }

            // Añadir el fragmento al container
            container.appendChild(fragment);

        })
        .catch(error => {
            // Detectar errores
            console.error('Error al obtener los asientos de la sesión', error);

            if (seccion_asientos) {
                seccion_asientos.innerHTML = `<p>Error al cargar los asientos. Por favor, inténtelo de nuevo</p>`;
            }
        });
}



// Limpia el div de asientos cada vez que se cambia de sesión
function limpiar_datos_asientos() {
    const inferior_izquierda = document.getElementById('inferior_izquierda');
    const columna_derecha = document.getElementById('columna_derecha');
    const inferior = document.getElementById('inferior');
    const container = document.getElementById('container');
    const container_asientos_selec = document.getElementById('container_asientos_selec');
    const listado_asientos_abajo = document.getElementById('listado_asientos_abajo');

    if (inferior_izquierda) {
        inferior_izquierda.innerHTML = "";
    }

    if (columna_derecha) {
        columna_derecha.innerHTML = "";
    }

    if (inferior) {
        inferior.innerHTML = "";
    }

    if (container) {
        container.innerHTML = "";
    }

    if (container_asientos_selec) {
        container_asientos_selec.innerHTML = "";
    }

    if (listado_asientos_abajo) {
        listado_asientos_abajo.innerHTML = "";
    }
}



// Generar leyenda de los asientos
function generar_leyenda() {
    const inferior = document.getElementById('inferior');
    inferior.innerHTML = "";
    const leyenda = document.createElement('ul');
    leyenda.classList.add('showcase');

    let elemento1_li = document.createElement('li');
    let elemento1_div = document.createElement('div');
    elemento1_div.classList.add('seat');
    let elemento1_small = document.createElement('div');
    elemento1_small.classList.add("leyenda_text");
    elemento1_small.innerHTML = "Disponible";
    elemento1_li.appendChild(elemento1_div);
    elemento1_li.appendChild(elemento1_small);

    let elemento2_li = document.createElement('li');
    let elemento2_div = document.createElement('div');
    elemento2_div.classList.add('seat');
    elemento2_div.classList.add('selected');
    let elemento2_small = document.createElement('div');
    elemento2_small.classList.add("leyenda_text");
    elemento2_small.innerHTML = "Seleccionado";
    elemento2_li.appendChild(elemento2_div);
    elemento2_li.appendChild(elemento2_small);

    let elemento3_li = document.createElement('li');
    let elemento3_div = document.createElement('div');
    elemento3_div.classList.add('seat');
    elemento3_div.classList.add('occupied');
    let elemento3_small = document.createElement('div');
    elemento3_small.classList.add("leyenda_text");
    elemento3_small.innerHTML = "Ocupado";
    elemento3_li.appendChild(elemento3_div);
    elemento3_li.appendChild(elemento3_small);

    leyenda.appendChild(elemento1_li);
    leyenda.appendChild(elemento2_li);
    leyenda.appendChild(elemento3_li);

    inferior.appendChild(leyenda);
}



// Generar los géneros de la película seleccionada
function generar_generos_sesion(datos_sesion) {
    const columna_derecha = document.getElementById('columna_derecha');
    columna_derecha.innerHTML = "";

    const generos = datos_sesion.pelicula.generos;

    generos.forEach(genero => {
        const genero_div = document.createElement('div');
        genero_div.classList.add('header_genero');
        genero_div.innerHTML = genero.genero;
        columna_derecha.appendChild(genero_div);
    });
}




// Generar los datos de la sesión seleccionada
function generar_datos_sesion(datos_sesion) {
    const superior_izquierda = document.getElementById('superior_izquierda');
    superior_izquierda.innerHTML = "";
    const inferior_izquierda = document.getElementById('inferior_izquierda');
    inferior_izquierda.innerHTML = "";
    const container = document.createElement('div');
    container.classList.add('movie-container');

    const fecha_div = document.createElement('div');
    fecha_div.classList.add('header_div');
    const fecha = document.createElement('div');
    fecha.classList.add('fecha_asientos');
    fecha.innerHTML = "<span class='header_titulo'>Fecha: </span> " + datos_sesion.fecha.fecha;
    fecha_div.appendChild(fecha);

    const hora_div = document.createElement('div');
    hora_div.classList.add('header_div');
    const hora = document.createElement('div');
    hora.classList.add('hora_asientos');
    hora.innerHTML = "<span class='header_titulo'>Hora: </span> " + datos_sesion.hora.hora;
    hora_div.appendChild(hora);

    const pelicula = document.createElement('div');
    pelicula.classList.add('pelicula_asientos');
    pelicula.innerHTML = datos_sesion.pelicula.titulo;

    const duracion_div = document.createElement('div');
    duracion_div.classList.add('header_div');
    const duracion = document.createElement('div');
    duracion.classList.add('duracion_asientos');
    duracion.innerHTML = "<span class='header_titulo'>Duración: </span> " + datos_sesion.pelicula.duracion + " min";
    duracion_div.appendChild(duracion);

    const sala_div = document.createElement('div');
    sala_div.classList.add('header_div');
    const sala = document.createElement('div');
    sala.classList.add('sala_asientos');
    sala.innerHTML = "<span class='header_titulo'>Sala: </span> " + datos_sesion.sesion.id_sala;
    sala_div.appendChild(sala);

    superior_izquierda.appendChild(pelicula);
    inferior_izquierda.appendChild(fecha_div);
    inferior_izquierda.appendChild(hora_div);
    inferior_izquierda.appendChild(sala_div);
    inferior_izquierda.appendChild(duracion_div);

}










// Seleccionar o deseleccionar un asiento
function seleccionar_asiento(datos_sesion, id_asiento) {
    // Recoger asiento seleccionado
    const asiento = document.getElementById(id_asiento);

    // Se recupera o se crea el listado de asientos seleccionados
    let container = document.getElementById('container_asientos_selec');
    if (!container) {
        const seccion_asientos = document.getElementById('seccion_asientos');

        container = document.createElement('div');
        container.classList.add('listado_asientos');
        container.classList.add('listado_asientos_borde');
        container.id = 'container_asientos_selec';

        const columna_izquierda = document.createElement('div');
        columna_izquierda.classList.add('columna_izquierda_lista');
        columna_izquierda.id = 'columna_izquierda_lista';

        const columna_derecha = document.createElement('div');
        columna_derecha.classList.add('columna_derecha_lista');
        columna_derecha.id = 'columna_derecha_lista';

        const abajo = document.createElement('div');
        abajo.classList.add('listado_asientos');
        abajo.id = 'listado_asientos_abajo';


        const columna_izquierda_abajo = document.createElement('div');
        columna_izquierda_abajo.classList.add('columna_izquierda_lista');
        columna_izquierda_abajo.id = 'columna_izquierda_abajo';

        const columna_derecha_abajo = document.createElement('div');
        columna_derecha_abajo.classList.add('columna_derecha_lista');
        columna_derecha_abajo.id = 'columna_derecha_abajo';

        container.appendChild(columna_izquierda);
        container.appendChild(columna_derecha);

        abajo.appendChild(columna_izquierda_abajo);
        abajo.appendChild(columna_derecha_abajo);

        seccion_asientos.appendChild(container);
        seccion_asientos.appendChild(abajo);
    }

    // Se cambia la clase del asientos seleccionado o deseleccionado
    if (asiento.classList.contains('available')) {
        asiento.classList.remove('available');
        asiento.classList.add('selected');
        anadir_asiento_selec(datos_sesion, id_asiento);
    } else if (asiento.classList.contains('selected')) {
        asiento.classList.remove('selected');
        asiento.classList.add('available');
        quitar_asiento_selec(id_asiento);
    }

}





function anadir_asiento_selec(datos_sesion, id_asiento) {
    // Recuperar los datos del contenedor y asientos
    const columna_izquierda_lista = document.getElementById('columna_izquierda_lista');
    const columna_derecha_lista = document.getElementById('columna_derecha_lista');
    const columna_izquierda_abajo = document.getElementById('columna_izquierda_abajo');
    const columna_derecha_abajo = document.getElementById('columna_derecha_abajo');

    const asiento = document.getElementById(id_asiento);
    const fila = asiento.dataset.fila;
    const columna = asiento.dataset.columna;
    const precio = 10;

    // Crear un ID único para el div del asiento seleccionado
    const asientoSelecId = `asiento_selec_${id_asiento}`;

    // Crear el div para la información del asiento seleccionado
    const fila_asiento_seleccionado_con_precio = document.createElement('div');
    fila_asiento_seleccionado_con_precio.classList.add('fila_asiento_seleccionado_con_precio');
    fila_asiento_seleccionado_con_precio.id = asientoSelecId;

    // Información del asiento
    const info_asiento_seleccionado = document.createElement('div');
    info_asiento_seleccionado.classList.add('info_asiento_seleccionado');
    info_asiento_seleccionado.id = 'asiento_selec_info_${id_asiento}';
    info_asiento_seleccionado.innerHTML = `
            Fila: ${fila}, &nbsp; Columna: ${columna}
    `;

    // Precio del asiento
    const precio_asiento_individual_lista = document.createElement('div');
    precio_asiento_individual_lista.classList.add('precio_asiento_individual_lista');
    precio_asiento_individual_lista.id = 'asiento_selec_precio_${id_asiento}';
    precio_asiento_individual_lista.innerHTML = `${precio} €`;

    // Añadir info y precio a div
    fila_asiento_seleccionado_con_precio.appendChild(info_asiento_seleccionado);
    fila_asiento_seleccionado_con_precio.appendChild(precio_asiento_individual_lista);

    // Si no hay título de Asientos Seleccionados, se añade
    let columna_izquierda_titulo = document.getElementById('columna_izquierda_titulo');
    if (!columna_izquierda_titulo) {
        columna_izquierda_titulo = document.createElement('div');
        columna_izquierda_titulo.classList.add('columna_izquierda_titulo');
        columna_izquierda_titulo.id = 'columna_izquierda_titulo';
        columna_izquierda_titulo.innerHTML = "Asientos Seleccionados";
        columna_izquierda_lista.appendChild(columna_izquierda_titulo);
    }

    // Se añade la fila del asiento seleccionado
    columna_izquierda_lista.appendChild(fila_asiento_seleccionado_con_precio);

    // Si no hay título de Precio Total, se añade
    let columna_derecha_titulo = document.getElementById('columna_derecha_titulo');
    if (!columna_derecha_titulo) {
        columna_derecha_titulo = document.createElement('div');
        columna_derecha_titulo.classList.add('columna_derecha_titulo');
        columna_derecha_titulo.id = 'columna_derecha_titulo';
        columna_derecha_titulo.innerHTML = "Precio Total";
        columna_derecha_abajo.appendChild(columna_derecha_titulo);
    }

    // Si no hay botón de CONFIRMAR SELECCIÓN, se añade
    let boton_confirmar_seleccion = document.getElementById('boton_confirmar_seleccion');
    if (!boton_confirmar_seleccion) {

        boton_confirmar_seleccion = document.createElement('button');
        boton_confirmar_seleccion.classList.add('confirmar_seleccion');
        boton_confirmar_seleccion.id = 'boton_confirmar_seleccion';
        boton_confirmar_seleccion.innerHTML = "Confirmar Selección";

        boton_confirmar_seleccion.addEventListener('click', function () {
            // Se limpia el correo de invitado guardado anteriormente en sessionStorage
            /* const email_invitado = sessionStorage.getItem('email_invitado');
            if (email_invitado) {
                sessionStorage.removeItem('email_invitado');
            } */

            // Si el usuario esta logueado se muestra el modal de Confirmar Selección
            if (datos_sesion.usuario) {
                confirmar_seleccion();
                // Si es invitado, se muestra el modal de Compra como Invitado
            } else {
                compra_como_invitado();
            }
        });

        columna_izquierda_abajo.appendChild(boton_confirmar_seleccion);
    }

    // Si no hay un Precio Total, se añade
    const num_asientos = document.querySelectorAll('.selected');
    const precio_total = (num_asientos.length - 1) * 10;
    let precio_total_div = document.getElementById('precio_total');
    if (!precio_total_div) {
        precio_total_div = document.createElement('div');
        precio_total_div.id = 'precio_total';
        precio_total_div.innerHTML = precio_total + " €";

        columna_derecha_abajo.appendChild(precio_total_div);
    } else {
        precio_total_div.innerHTML = precio_total + " €";
    }

    // Si no hay borde, se añade
    const container_asientos_selec = document.getElementById('container_asientos_selec');
    if (!container_asientos_selec.classList.contains('listado_asientos_borde')) {
        container_asientos_selec.classList.add('listado_asientos_borde');
    }

}







function quitar_asiento_selec(id_asiento) {
    // Seleccionar el asiento a eliminar de la lista
    const asientoSelecId = `asiento_selec_${id_asiento}`;
    const asientoDiv = document.getElementById(asientoSelecId);

    // Se elimina el asiento seleccionado si existe
    if (asientoDiv) {
        asientoDiv.remove();
    }

    // Seleccionar el precio a eliminar de la lista
    const asientoSelecPrecioId = `precio_selec_${id_asiento}`;
    const precioDiv = document.getElementById(asientoSelecPrecioId);

    // Se elimina el asiento seleccionado si existe
    if (precioDiv) {
        precioDiv.remove();
    }

    // Se actualiza el precio total
    const num_asientos = document.querySelectorAll('.selected');
    const precio_total = (num_asientos.length - 1) * 10;
    const precio_total_div = document.getElementById('precio_total');
    precio_total_div.innerHTML = precio_total + " €";

    // Si no hay ningún asiento seleccionado, se eliminan los elementos de la lista
    const comprobar_asientos_seleccionados = document.querySelectorAll('.selected');

    if (comprobar_asientos_seleccionados.length <= 1) {
        const container_asientos_selec = document.getElementById('container_asientos_selec');
        container_asientos_selec.classList.remove('listado_asientos_borde');

        const columna_izquierda_titulo = document.getElementById('columna_izquierda_titulo');
        columna_izquierda_titulo.remove();

        const columna_derecha_titulo = document.getElementById('columna_derecha_titulo');
        columna_derecha_titulo.remove();

        const boton_confirmar_seleccion = document.getElementById('boton_confirmar_seleccion');
        boton_confirmar_seleccion.remove();

        const precio_total = document.getElementById('precio_total');
        precio_total.remove();
    }
}
















/////////////////////////////////////////////////// CONFIRMAR SELECCIÓN ///////////////////////////////////////////////////



// Confirmar la selección de los asientos -> Se accede al clicar el botón CONFIRMAR SELECCIÓN
window.confirmar_seleccion = function () {
    // Recuperar los asientos con el atributo 'selected'
    const asientos_con_ejemplo = document.querySelectorAll('.selected');
    let id_sesion;

    // Filtrar los asientos que no tengan id (quitar el asiento 'selected' de la leyenda)
    let asientos = new Set();

    asientos_con_ejemplo.forEach(asiento => {
        if (asiento.id) {
            asientos.add(asiento);
        }

        if (!id_sesion && asiento.dataset.id_sesion) {
            id_sesion = asiento.dataset.id_sesion;
        }
    });

    const recuperar_sesion = `/recuperar_sesion/id_sesion=${id_sesion}`;

    fetch(recuperar_sesion)
        .then(response => {
            // Comprobar si la respuesta fue exitosa
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return response.json();
        })
        .then(datos_sesion => {
            // Comprobar que tenemos los datos de la sesión
            if (!datos_sesion) {
                throw new error("Error al recuperar los asientos seleccionados");
            }

            // Comprobar que tenemos los asientos seleccionados
            if (!asientos) {
                throw new error("Error al recuperar los asientos seleccionados");
            }

            // Mostrar el modal de confirmar selección
            mostrar_modal_confirmar(asientos, datos_sesion);
        })
        .catch(error => {
            console.error('Error al obtener los datos de las sesiones:', error);

            if (seccion_sesiones) {
                seccion_sesiones.innerHTML = `<p>Error al procesar los asientos seleccionados. Por favor, inténtelo de nuevo</p>`;
            }
        });

}


// Mostrar el modal de confirmar selección
function mostrar_modal_confirmar(asientos, datos_sesion) {
    const modal_confirmar_seleccion = document.getElementById('modal_confirmar_seleccion');
    const modal_confirmar_seleccion_form = document.getElementById('modal_confirmar_seleccion_form');

    if (modal_confirmar_seleccion.classList.contains('hidden')) {
        modal_confirmar_seleccion.classList.remove('hidden');
        modal_confirmar_seleccion.classList.add('visible');
        document.body.classList.add('modal_abierto');
    }

    if (modal_confirmar_seleccion_form) {
        crear_estructura(modal_confirmar_seleccion_form);
        generar_confirmar_titulo();
        generar_confirmar_body(datos_sesion);
        generar_confirmar_entradas(datos_sesion, asientos);
        generar_confirmar_botones(datos_sesion, asientos);
        generar_input_ocultos(datos_sesion, asientos);
    }

}

// Crear la estructura del modal de forma dinámica
function crear_estructura(modal_confirmar_seleccion) {

    // Container grande
    let confirmar_container = document.createElement('div');
    confirmar_container.classList.add('confirmar_container');
    confirmar_container.id = 'confirmar_container';

    // Div título
    let confirmar_container_titulo = document.createElement('div');
    confirmar_container_titulo.classList.add('confirmar_container_titulo');
    confirmar_container_titulo.id = 'confirmar_container_titulo';

    // Div body
    let confirmar_container_body = document.createElement('div');
    confirmar_container_body.classList.add('confirmar_container_body');
    confirmar_container_body.id = 'confirmar_container_body';

    // Div entradas
    let confirmar_container_entradas = document.createElement('div');
    confirmar_container_entradas.classList.add('confirmar_container_entradas');
    confirmar_container_entradas.id = 'confirmar_container_entradas';

    // Div separador 1
    let confirmar_container_separador1 = document.createElement('div');
    confirmar_container_separador1.classList.add('confirmar_container_separador');

    // Div separador 2
    let confirmar_container_separador2 = document.createElement('div');
    confirmar_container_separador2.classList.add('confirmar_container_separador');

    confirmar_container.appendChild(confirmar_container_titulo);
    confirmar_container.appendChild(confirmar_container_separador1);
    confirmar_container.appendChild(confirmar_container_body);
    confirmar_container.appendChild(confirmar_container_separador2);
    confirmar_container.appendChild(confirmar_container_entradas);

    modal_confirmar_seleccion.appendChild(confirmar_container);

}



// Generar título del modal
function generar_confirmar_titulo() {
    const titulo_div = document.getElementById('confirmar_container_titulo');

    if (!titulo_div) {
        return;
    }

    let titulo = document.createElement('div');
    titulo.classList.add('confirmar_titulo');
    titulo.id = 'confirmar_titulo';
    titulo.innerHTML = "Confirmar Selección de Entradas";

    titulo_div.appendChild(titulo);
}

// Generar cartel y datos de la sesión seleccionada
function generar_confirmar_body(datos_sesion) {
    const confirmar_container_body = document.getElementById('confirmar_container_body');

    if (!confirmar_container_body) {
        return;
    }

    // Cartel de película
    let cartel_div = document.createElement('div');
    cartel_div.classList.add('confirmar_cartel_div');
    cartel_div.id = 'confirmar_cartel_div';

    let cartel_img = document.createElement('img');
    cartel_img.classList.add('confirmar_cartel_img');
    cartel_img.id = 'confirmar_cartel_img';
    cartel_img.setAttribute('src', datos_sesion.pelicula.poster_url);
    cartel_img.setAttribute('loading', "lazy");
    cartel_img.setAttribute('alt', datos_sesion.pelicula.titulo);

    cartel_div.appendChild(cartel_img);

    // Etiquetas para los datos de la sesión
    // Div general
    let sesion_titulos_div = document.createElement('div');
    sesion_titulos_div.classList.add('sesion_titulos_div');
    sesion_titulos_div.id = 'sesion_titulos_div';

    // Etiqueta título
    let sesion_titulos_titulo = document.createElement('div');
    sesion_titulos_titulo.classList.add('sesion_titulos');
    sesion_titulos_titulo.id = 'sesion_titulos_titulo';
    sesion_titulos_titulo.innerHTML = "Título: ";

    // Etiqueta fecha
    let sesion_titulos_fecha = document.createElement('div');
    sesion_titulos_fecha.classList.add('sesion_titulos');
    sesion_titulos_fecha.id = 'sesion_titulos_fecha';
    sesion_titulos_fecha.innerHTML = "Fecha: ";

    // Etiqueta dia
    let sesion_titulos_dia = document.createElement('div');
    sesion_titulos_dia.classList.add('sesion_titulos');
    sesion_titulos_dia.id = 'sesion_titulos_dia';
    sesion_titulos_dia.innerHTML = "Día: ";

    // Etiqueta hora
    let sesion_titulos_hora = document.createElement('div');
    sesion_titulos_hora.classList.add('sesion_titulos');
    sesion_titulos_hora.id = 'sesion_titulos_hora';
    sesion_titulos_hora.innerHTML = "Hora: ";

    // Etiqueta sala
    let sesion_titulos_sala = document.createElement('div');
    sesion_titulos_sala.classList.add('sesion_titulos');
    sesion_titulos_sala.id = 'sesion_titulos_sala';
    sesion_titulos_sala.innerHTML = "Sala: ";

    // Etiqueta duración
    let sesion_titulos_duracion = document.createElement('div');
    sesion_titulos_duracion.classList.add('sesion_titulos');
    sesion_titulos_duracion.id = 'sesion_titulos_duracion';
    sesion_titulos_duracion.innerHTML = "Duración: ";

    // Añadir titulos al div
    sesion_titulos_div.appendChild(sesion_titulos_titulo);
    sesion_titulos_div.appendChild(sesion_titulos_fecha);
    sesion_titulos_div.appendChild(sesion_titulos_dia);
    sesion_titulos_div.appendChild(sesion_titulos_hora);
    sesion_titulos_div.appendChild(sesion_titulos_sala);
    sesion_titulos_div.appendChild(sesion_titulos_duracion);


    // Generar datos de la sesión seleccionada
    // Div general
    let sesion_datos_div = document.createElement('div');
    sesion_datos_div.classList.add('sesion_datos_div');
    sesion_datos_div.id = 'sesion_datos_div';

    // Título
    let sesion_datos_titulo = document.createElement('div');
    sesion_datos_titulo.classList.add('sesion_datos');
    sesion_datos_titulo.id = 'sesion_datos_titulo';
    sesion_datos_titulo.innerHTML = datos_sesion.pelicula.titulo;

    // Fecha
    let sesion_datos_fecha = document.createElement('div');
    sesion_datos_fecha.classList.add('sesion_datos');
    sesion_datos_fecha.id = 'sesion_datos_fecha';
    sesion_datos_fecha.innerHTML = datos_sesion.fecha.fecha;

    // Fecha
    let sesion_datos_dia = document.createElement('div');
    sesion_datos_dia.classList.add('sesion_datos');
    sesion_datos_dia.id = 'sesion_datos_dia';
    sesion_datos_dia.innerHTML = datos_sesion.dia_semana;

    // Hora
    let sesion_datos_hora = document.createElement('div');
    sesion_datos_hora.classList.add('sesion_datos');
    sesion_datos_hora.id = 'sesion_datos_hora';
    sesion_datos_hora.innerHTML = datos_sesion.hora.hora;

    // Sala
    let sesion_datos_sala = document.createElement('div');
    sesion_datos_sala.classList.add('sesion_datos');
    sesion_datos_sala.id = 'sesion_datos_sala';
    sesion_datos_sala.innerHTML = datos_sesion.sala.id_sala;

    // Duración
    let sesion_datos_duracion = document.createElement('div');
    sesion_datos_duracion.classList.add('sesion_datos');
    sesion_datos_duracion.id = 'sesion_datos_duracion';
    sesion_datos_duracion.innerHTML = datos_sesion.pelicula.duracion + " min";

    // Añadir datos al div
    sesion_datos_div.appendChild(sesion_datos_titulo);
    sesion_datos_div.appendChild(sesion_datos_fecha);
    sesion_datos_div.appendChild(sesion_datos_dia);
    sesion_datos_div.appendChild(sesion_datos_hora);
    sesion_datos_div.appendChild(sesion_datos_sala);
    sesion_datos_div.appendChild(sesion_datos_duracion);


    // Añadir todo al contenedor principal
    confirmar_container_body.appendChild(cartel_div);
    confirmar_container_body.appendChild(sesion_titulos_div);
    confirmar_container_body.appendChild(sesion_datos_div);
}

// Generar asientos seleccionados
function generar_confirmar_entradas(datos_sesion, asientos) {
    // Recuperar usuario (si hay)
    const usuario = datos_sesion.usuario;

    const confirmar_container_entradas = document.getElementById('confirmar_container_entradas');

    // Div superior de asientos
    let asiento_div_superior = document.createElement('div');
    asiento_div_superior.classList.add('asiento_div_superior');
    asiento_div_superior.id = 'asiento_div_superior';

    // Título de div
    let entradas_titulo = document.createElement('div');
    entradas_titulo.classList.add('entradas_titulo');
    entradas_titulo.id = 'entradas_titulo';
    entradas_titulo.innerHTML = "Resumen de Compra";

    // Div de botones
    let confirmar_container_botones = document.createElement('div');
    confirmar_container_botones.classList.add('confirmar_container_botones');
    confirmar_container_botones.id = 'confirmar_container_botones';

    // Div inferior de asientos
    let asiento_div_inferior = document.createElement('div');
    asiento_div_inferior.classList.add('asiento_div');
    asiento_div_inferior.id = 'asiento_div';

    // Div de asientos seleccionados
    let asiento_div_asientos = document.createElement('div');
    asiento_div_asientos.classList.add('asiento_div_columna');
    asiento_div_asientos.id = 'asiento_div_asientos';

    // Div de precio de asientos
    let asiento_div_precio = document.createElement('div');
    asiento_div_precio.classList.add('asiento_div_columna');
    asiento_div_precio.id = 'asiento_div_precio';

    // Columna de 'Nº Fila: '
    let asiento_columna_fila = document.createElement('div');
    asiento_columna_fila.classList.add('asiento_columna');
    asiento_columna_fila.id = 'asiento_columna_fila';

    // Columna de Nº Fila
    let asiento_columna_numfila = document.createElement('div');
    asiento_columna_numfila.classList.add('asiento_columna');
    asiento_columna_numfila.id = 'asiento_columna_numfila';

    // Columna de 'Nº Columna: '
    let asiento_columna_columna = document.createElement('div');
    asiento_columna_columna.classList.add('asiento_columna');
    asiento_columna_columna.id = 'asiento_columna_columna';

    // Columna de Nº Columna
    let asiento_columna_numcolumna = document.createElement('div');
    asiento_columna_numcolumna.classList.add('asiento_columna');
    asiento_columna_numcolumna.id = 'asiento_columna_numcolumna';

    // Columna de Precio
    let asiento_columna_precio = document.createElement('div');
    asiento_columna_precio.classList.add('asiento_columna');
    asiento_columna_precio.id = 'asiento_columna_precio';


    asientos.forEach(asiento => {
        // Asiento Seleccionado ->
        let columna_asiento_asiento = document.createElement('div');
        columna_asiento_asiento.classList.add('asiento_columna_texto');
        columna_asiento_asiento.id = 'columna_asiento_asiento_' + asiento.id;
        columna_asiento_asiento.innerHTML = "- Asiento: ";

        // Fila:
        let columna_asiento_fila = document.createElement('div');
        columna_asiento_fila.classList.add('asiento_columna_texto');
        columna_asiento_fila.id = 'columna_asiento_fila_' + asiento.id;
        columna_asiento_fila.innerHTML = "Fila: ";
        asiento_columna_fila.appendChild(columna_asiento_fila);

        // Nº Fila
        let columna_asiento_numfila = document.createElement('div');
        columna_asiento_numfila.classList.add('asiento_columna_texto');
        columna_asiento_numfila.id = 'columna_asiento_numfila_' + asiento.id;
        columna_asiento_numfila.innerHTML = asiento.dataset.fila;
        asiento_columna_numfila.appendChild(columna_asiento_numfila);

        // Columna: 
        let columna_asiento_columna = document.createElement('div');
        columna_asiento_columna.classList.add('asiento_columna_texto');
        columna_asiento_columna.id = 'columna_asiento_columna_' + asiento.id;
        columna_asiento_columna.innerHTML = "Columna: ";
        asiento_columna_columna.appendChild(columna_asiento_columna);

        // Nº Columna
        let columna_asiento_numcolumna = document.createElement('div');
        columna_asiento_numcolumna.classList.add('asiento_columna_texto');
        columna_asiento_numcolumna.id = 'columna_asiento_numcolumna_' + asiento.id;
        columna_asiento_numcolumna.innerHTML = asiento.dataset.columna;
        asiento_columna_numcolumna.appendChild(columna_asiento_numcolumna);

        // Precio
        let columna_asiento_precio = document.createElement('div');
        columna_asiento_precio.classList.add('asiento_columna_texto');
        columna_asiento_precio.id = 'columna_asiento_precio_' + asiento.id;
        columna_asiento_precio.innerHTML = "10 €";
        asiento_columna_precio.appendChild(columna_asiento_precio);
    });

    // Div de Precio Títulos
    let asiento_columna_preciotitulos = document.createElement('div');
    asiento_columna_preciotitulos.classList.add('asiento_columna');
    asiento_columna_preciotitulos.id = 'asiento_columna_preciotitulos';


    // Div de Precio Datos
    let asiento_columna_preciodatos = document.createElement('div');
    asiento_columna_preciodatos.classList.add('asiento_columna');
    asiento_columna_preciodatos.id = 'asiento_columna_preciodatos';

    // Título Precio
    let div_precio_titulo = document.createElement('div');
    div_precio_titulo.classList.add('asiento_columna_texto');
    div_precio_titulo.id = 'div_precio_titulo';
    div_precio_titulo.innerHTML = "Precio Total: ";


    // Calcular precio total (PENDIENTE PRECIO ASIENTO DE BBDD)
    const precio_asientos = asientos.size * 10;

    // Precio Total
    let div_precio_preciototal = document.createElement('div');
    div_precio_preciototal.classList.add('asiento_columna_texto');
    div_precio_preciototal.id = 'div_precio_preciototal';
    div_precio_preciototal.innerHTML = precio_asientos + " €";

    // Recuperar descuento de usuario
    let descuento_aplicado_cantidad;
    let descuento_aplicado;
    // Se recupera y procesa el tipo de usuario. Si es invitado, se muestra adecuadamente
    if (usuario) {
        descuento_aplicado_cantidad = datos_sesion.descuento.descuento;
        let tipo_usuario = datos_sesion.descuento.tipo;
        tipo_usuario = tipo_usuario.charAt(0).toUpperCase() + tipo_usuario.slice(1);
        descuento_aplicado = tipo_usuario + " (" + descuento_aplicado_cantidad + "% )";
    } else {
        descuento_aplicado_cantidad = 0;
        descuento_aplicado = "Invitado ( " + descuento_aplicado_cantidad + "% )";
    }

    // Título Descuento Aplicado
    let div_descuento_titulo = document.createElement('div');
    div_descuento_titulo.classList.add('asiento_columna_texto');
    div_descuento_titulo.id = 'div_descuento_titulo';
    div_descuento_titulo.innerHTML = "Descuento Aplicado: ";

    // Descuento aplicado
    let div_descuento_descuento = document.createElement('div');
    div_descuento_descuento.classList.add('asiento_columna_texto');
    div_descuento_descuento.id = 'div_descuento_descuento';
    div_descuento_descuento.innerHTML = descuento_aplicado;

    // Título Precio Final
    let div_preciofinal_titulo = document.createElement('div');
    div_preciofinal_titulo.classList.add('asiento_columna_texto');
    div_preciofinal_titulo.id = 'div_preciofinal_titulo';
    div_preciofinal_titulo.innerHTML = "Precio Final: ";

    // Calcular precio final mediante el descuento
    const precio_descontado = precio_asientos * (descuento_aplicado_cantidad / 100);
    const precio_final = precio_asientos - precio_descontado;

    // Precio Final
    let div_preciofinal_preciofinal = document.createElement('div');
    div_preciofinal_preciofinal.classList.add('asiento_columna_textoprecio');
    div_preciofinal_preciofinal.id = 'div_preciofinal_preciofinal';
    div_preciofinal_preciofinal.innerHTML = precio_final + " €";

    // Guardar en datos_sesion
    datos_sesion.precio_asientos = precio_asientos;
    datos_sesion.precio_descuento = descuento_aplicado_cantidad;
    datos_sesion.precio_final = precio_final;

    // Añadir columnas a div superior
    asiento_div_superior.appendChild(entradas_titulo);
    asiento_div_superior.appendChild(confirmar_container_botones);

    // Añadir columnas a div inferior Asientos
    asiento_div_asientos.appendChild(asiento_columna_fila);
    asiento_div_asientos.appendChild(asiento_columna_numfila);
    asiento_div_asientos.appendChild(asiento_columna_columna);
    asiento_div_asientos.appendChild(asiento_columna_numcolumna);
    asiento_div_asientos.appendChild(asiento_columna_precio);

    // Añadir Títulos de Precio y Descuento
    asiento_columna_preciotitulos.appendChild(div_precio_titulo);
    asiento_columna_preciotitulos.appendChild(div_descuento_titulo);
    asiento_columna_preciotitulos.appendChild(div_preciofinal_titulo);

    // Añadir datos de Precio y Descuento
    asiento_columna_preciodatos.appendChild(div_precio_preciototal);
    asiento_columna_preciodatos.appendChild(div_descuento_descuento);
    asiento_columna_preciodatos.appendChild(div_preciofinal_preciofinal);

    // Añadir div interiores a Div Precios
    asiento_div_precio.appendChild(asiento_columna_preciotitulos);
    asiento_div_precio.appendChild(asiento_columna_preciodatos);

    // Añadir div interiores a div asientos
    asiento_div_inferior.appendChild(asiento_div_asientos);
    asiento_div_inferior.appendChild(asiento_div_precio);

    // Añadir todo al contenedor principal
    confirmar_container_entradas.appendChild(asiento_div_superior);
    confirmar_container_entradas.appendChild(asiento_div_inferior);

}

// Generar botones de Volver y Continuar con el Pago
function generar_confirmar_botones(datos_sesion, asientos) {
    // Recuperar div de botones
    const confirmar_container_botones = document.getElementById('confirmar_container_botones');

    if (!confirmar_container_botones) {
        confirmar_container_botones.innerHTML('Error al cargar los botones. Inténtelo de nuevo más tarde')
        return;
    }

    // Botón Volver div
    let boton_volver_div = document.createElement('div');
    boton_volver_div.classList.add('boton_confirmar');
    boton_volver_div.id = 'boton_volver_div';

    // Botón Volver
    let boton_volver = document.createElement('button');
    boton_volver.classList.add('boton_volver');
    boton_volver.id = 'boton_volver';
    boton_volver.innerHTML = "Volver";

    // Añadir evento al botón Volver
    boton_volver.addEventListener('click', function () {
        cerrar_y_limpiar_modal_confirmar();
    });

    // Botón Continuar div
    let boton_continuar_div = document.createElement('div');
    boton_continuar_div.classList.add('boton_confirmar');
    boton_continuar_div.id = 'boton_continuar_div';

    // Botón Continuar
    let boton_continuar = document.createElement('button');
    boton_continuar.setAttribute('type', 'submit');
    boton_continuar.classList.add('boton_continuar');
    boton_continuar.id = 'boton_continuar';
    boton_continuar.innerHTML = "Proceder con el Pago";

    // Añadir evento al botón Continuar
   /*  boton_continuar.addEventListener('click', function () {
        const modal_confirmar_seleccion = document.querySelector('.modal_confirmar');

        if (modal_confirmar_seleccion && modal_confirmar_seleccion.classList.contains('visible')) {
            modal_confirmar_seleccion.classList.remove('visible');
            modal_confirmar_seleccion.classList.add('hidden');
            document.body.classList.remove('modal_abierto');
            modal_confirmar_seleccion.innerHTML = "";
        }

        // Guardar los valores de la sesión y asientos seleccionados en el navegador
        // Se guarda por si hay errores en el pago y se recarga la página, poder recuperarlos
        // Se guarda la sesión
        localStorage.setItem('datos_sesion', JSON.stringify(datos_sesion));

        // Se convierten los asientos seleccionados a array
        const asientos_array = [];
        asientos.forEach(asiento => {
            const asiento_datos = {
                fila: asiento.dataset.fila,
                columna: asiento.dataset.columna,
                sesion: asiento.dataset.sesion,
                id: asiento.dataset.id,
                estado: asiento.estado,
            };
            asientos_array.push(asiento_datos);
        });

        // Se guardan los asientos
        localStorage.setItem('asientos', JSON.stringify(asientos_array));


        mostrar_modal_pago(datos_sesion, asientos);
    }); */

    // Añadir botones a sus div
    boton_volver_div.appendChild(boton_volver);
    boton_continuar_div.appendChild(boton_continuar);

    // Añadir div de botones al div principal de botones
    confirmar_container_botones.appendChild(boton_volver_div);
    confirmar_container_botones.appendChild(boton_continuar_div);
}






function generar_input_ocultos(datos_sesion, asientos) {
    // Guardar la sesión y asientos para el formulario
    // Si ya tienen valores, se borran
    // Asientos (se generan tantos como haya)
    const asientos_div = document.getElementById('asientos_div');
    asientos_div.innerHTML = "";
    asientos.forEach(asiento => {
        const asiento_input = document.createElement('input');
        asiento_input.setAttribute('type', 'hidden');
        asiento_input.setAttribute('name', 'asiento[]');
        asiento_input.setAttribute('value', asiento.id);
        asientos_div.appendChild(asiento_input);
    });

    // Sesión
    const datos_sesion_div = document.getElementById('datos_sesion_div');
    datos_sesion_div.innerHTML = "";
    const datos_sesion_input = document.createElement('input');
    datos_sesion_input.setAttribute('type', 'hidden');
    datos_sesion_input.setAttribute('name', 'sesion_id');
    datos_sesion_input.setAttribute('value', datos_sesion.id);
    datos_sesion_div.appendChild(datos_sesion_input);

    // Email de invitado (si existe)
    const email_invitado = sessionStorage.getItem('email_invitado');

    if (email_invitado) {
        const email_invitado_input = document.createElement('input');
        email_invitado_input.setAttribute('type', 'hidden');
        email_invitado_input.setAttribute('name', 'email_invitado');
        email_invitado_input.setAttribute('value', email_invitado);
        datos_sesion_div.appendChild(email_invitado_input);
    }

    // Precio
    const precio_div = document.getElementById('precio_div');
    precio_div.innerHTML = "";

    // Precio Total
    const precio_total_input = document.createElement('input');
    precio_total_input.setAttribute('type', 'hidden');
    precio_total_input.setAttribute('name', 'precio_total');
    precio_total_input.setAttribute('value', datos_sesion.precio_asientos);
    // Descuento
    const precio_descuento_input = document.createElement('input');
    precio_descuento_input.setAttribute('type', 'hidden');
    precio_descuento_input.setAttribute('name', 'precio_descuento');
    precio_descuento_input.setAttribute('value', datos_sesion.precio_descuento);
    // Precio Final
    const precio_final_input = document.createElement('input');
    precio_final_input.setAttribute('type', 'hidden');
    precio_final_input.setAttribute('name', 'precio_final');
    precio_final_input.setAttribute('value', datos_sesion.precio_final);


    precio_div.appendChild(precio_total_input);
    precio_div.appendChild(precio_descuento_input);
    precio_div.appendChild(precio_final_input);
}





const modal_confirmar_seleccion_div = document.getElementById('modal_confirmar_seleccion');
const modal_confirmar_seleccion_form = document.getElementById('modal_confirmar_seleccion_form');

function cerrar_y_limpiar_modal_confirmar() {
    if (modal_confirmar_seleccion_div && modal_confirmar_seleccion_div.classList.contains('visible')) {
        modal_confirmar_seleccion_div.classList.remove('visible');
        modal_confirmar_seleccion_div.classList.add('hidden');
        document.body.classList.remove('modal_abierto');

        const formElement = document.getElementById('modal_confirmar_seleccion_form');
        if (formElement) {
            // Elimina solo el 'confirmar_container' que se añade dinámicamente
            const confirmarContainer = formElement.querySelector('#confirmar_container');
            if (confirmarContainer) {
                confirmarContainer.remove();
            }
        }
    }
}

// Funcionalidades para cerrar el modal
if (modal_confirmar_seleccion_div) {
    // Para que el modal confirmar se cierre al presionar Escape (también se limpia de datos)
    document.addEventListener('keydown', function (event) {
        if ((event.key === 'Escape' || event.keyCode === 27) && modal_confirmar_seleccion_div.classList.contains('visible')) {
            event.preventDefault();
            cerrar_y_limpiar_modal_confirmar();
        }
    });

    modal_confirmar_seleccion_form.addEventListener('click', function (event) {
        // Para que el modal confirmar se cierre al clicar fuera del modal (también se limpia de datos)
        if (event.target === modal_confirmar_seleccion_form && modal_confirmar_seleccion_div.classList.contains('visible')) {
            cerrar_y_limpiar_modal_confirmar();
        }
    });
}








/////////////////////////////////////////////////// COMPRA COMO INVITADO ///////////////////////////////////////////////////


// Modal de Comprar como Invitado
window.compra_como_invitado = function () {
    const modal_invitado = document.getElementById('modal_invitado');

    if (!modal_invitado) {
        return;
    }

    if (modal_invitado.classList.contains('hidden')) {
        modal_invitado.classList.remove('hidden');
        modal_invitado.classList.add('visible');
        document.body.classList.add('modal_abierto');
    }
}



// Añadir eventos a botones de Volver y Continuar como Invitado
if (modal_invitado) {
    const modal_invitado = document.getElementById('modal_invitado');
    const boton_invitado_volver = document.getElementById('boton_invitado_volver');
    const boton_invitado_continuar = document.getElementById('boton_invitado_continuar');
    const input_email_invitado = document.getElementById('email_invitado');

    // Añadir evento al botón Volver
    boton_invitado_volver.addEventListener('click', function (event) {
        event.preventDefault();

        if (modal_invitado && modal_invitado.classList.contains('visible')) {
            modal_invitado.classList.remove('visible');
            modal_invitado.classList.add('hidden');
            document.body.classList.remove('modal_abierto');
        }
    });

    // Clicar en el botón Continuar como Invitado
    boton_invitado_continuar.addEventListener('click', function (event) {

        event.preventDefault();

        // Limpiar errores anteriores
        quitar_error_email();

        // Limpiar el input introducido
        const email_invitado = input_email_invitado.value.trim();

        // Validación 1: No está vacío
        if (email_invitado === '') {
            mostrar_error_email('Por favor, introduce tu dirección de correo electrónico.');
            return;
        }

        // Validación 2: Formato de email válido
        if (!email_valido(email_invitado)) {
            mostrar_error_email('Por favor, introduce una dirección de correo electrónico válida.');
            return; // Detener la ejecución
        }

        // Si ambas validaciones pasan:
        if (modal_invitado && modal_invitado.classList.contains('visible')) {
            modal_invitado.classList.remove('visible');
            modal_invitado.classList.add('hidden');
            document.body.classList.remove('modal_abierto');
        }

        // Guardar el email de invitado
        sessionStorage.setItem('email_invitado', email_invitado);

        confirmar_seleccion();
    });
}


// Comprobar que el email de invitado es válido
function email_valido(email) {
    // Expresión regular simple para validar formato de email.
    // Puedes usar una más compleja si necesitas una validación más estricta.
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}





function mostrar_error_email(mensaje) {
    // 1. Quitar errores previos
    let errorExistente = document.getElementById('error_email_invitado');
    if (errorExistente) {
        errorExistente.remove();
    }

    // 2. Crear y añadir el nuevo mensaje de error
    const divCorreo = document.querySelector('.invitado_correo');
    if (divCorreo) {
        const pError = document.createElement('p');
        pError.id = 'error_email_invitado';
        pError.textContent = mensaje;
        pError.style.color = 'red';
        pError.style.fontSize = '0.9em';
        pError.style.marginTop = '5px';
        divCorreo.appendChild(pError);
    }
    inputEmailInvitado.focus();
}






// Quita el mensaje de error del email de invitado
function quitar_error_email() {
    let errorExistente = document.getElementById('error_email_invitado');
    if (errorExistente) {
        errorExistente.remove();
    }
}



// Para que el modal Comprar como Invitado se cierre al presionar Escape (también se limpia de datos)
if (modal_invitado) {
    document.addEventListener('keydown', function (event) {
        if ((event.key === 'Escape' || event.keyCode === 27) && modal_invitado.classList.contains('visible')) {

            event.preventDefault();

            // Ocultar el modal y limpiar los errores de email
            modal_invitado.classList.remove('visible');
            modal_invitado.classList.add('hidden');
            document.body.classList.remove('modal_abierto');
            quitar_error_email();
        }
    });
}


// Para que el modal Comprar como Invitado se cierre al clicar fuera del modal (también se limpia de datos)
if (modal_invitado) {
    modal_invitado.addEventListener('click', function (event) {
        if (event.target === modal_invitado && modal_invitado.classList.contains('visible')) {
            modal_invitado.classList.remove('visible');
            modal_invitado.classList.add('hidden');
            document.body.classList.remove('modal_abierto');
        }
    });
}


