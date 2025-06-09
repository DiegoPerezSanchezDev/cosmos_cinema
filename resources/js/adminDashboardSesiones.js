document.addEventListener('DOMContentLoaded', function() {
    // Selectores de elementos del DOM
    const sessionFechaSelect = document.getElementById('session-fecha');
    const sessionSalaSelect = document.getElementById('session-sala');
    const sessionPeliculaSelect = document.getElementById('session-pelicula');
    const sessionHoraSelect = document.getElementById('session-hora');
    const createSessionForm = document.getElementById('create-session-form');
    const sessionCreationMessage = document.getElementById('session-creation-message');
    const createSessionSection = document.getElementById('create-session-section'); // Para gestión de secciones SPA

    // Selectores de la tabla de sesiones
    const sessionsTableBody = document.querySelector('#sessionsTable tbody');
    const sessionsTable = document.getElementById('sessionsTable');
    const noSessionsMessage = document.getElementById('noSessionsMessage');

    // NUEVO: Selector para el elemento donde se mostrará la fecha de la sesión
    const selectedSessionDateSpan = document.getElementById('selected-session-date');

    // Variable para controlar si los datos iniciales de la sección de sesiones ya se cargaron
    let sessionDataLoaded = false;

    // Variable para almacenar el ID del temporizador del mensaje y poder limpiarlo
    let messageTimeoutId = null; 

    /**
     * Función para mostrar mensajes temporales (carga, éxito, error).
     * @param {string} message - El texto del mensaje a mostrar.
     * @param {'success' | 'error' | 'info'} type - El tipo de mensaje (determina el color). 'info' para carga.
     * @param {number} duration - Duración en milisegundos antes de que el mensaje se borre (0 para no borrar automáticamente).
     */
    function showTemporaryMessage(message, type = 'success', duration = 3000) {
        // Limpiar cualquier temporizador de mensaje anterior para evitar conflictos
        if (messageTimeoutId) {
            clearTimeout(messageTimeoutId);
            messageTimeoutId = null;
        }

        sessionCreationMessage.textContent = message;
        // Asignar color basado en el tipo de mensaje
        sessionCreationMessage.style.color = type === 'success' ? 'green' : (type === 'error' ? 'red' : 'orange'); // 'info' (carga) será naranja
        
        // Asegurarse de que el elemento sea visible (en caso de que CSS lo oculte por defecto)
        sessionCreationMessage.style.display = 'block'; 
        sessionCreationMessage.style.opacity = '1'; 

        // Si se especifica una duración mayor a 0, configurar el temporizador para borrar el mensaje
        if (duration > 0) {
            messageTimeoutId = setTimeout(() => {
                sessionCreationMessage.textContent = '';
                sessionCreationMessage.style.color = '';
                sessionCreationMessage.style.display = 'none'; // Ocultar de nuevo
                sessionCreationMessage.style.opacity = '0'; // Transparencia a 0 si se usa transición
                messageTimeoutId = null; // Limpiar el ID del temporizador después de que se ejecuta
            }, duration);
        }
    }

    /**
     * Función genérica para realizar peticiones Fetch a la API.
     * @param {string} url - La URL del endpoint.
     * @param {string} method - El método HTTP (GET, POST, PUT, DELETE).
     * @param {Object|null} data - Los datos a enviar en el cuerpo de la petición (para POST, PUT, PATCH, DELETE).
     * @returns {Promise<Object|boolean>} - La respuesta JSON o true si es 204 No Content.
     * @throws {Error} Si la respuesta no es OK.
     */
    async function fetchData(url, method = 'GET', data = null) {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // Token CSRF para Laravel
            }
        };
        // Adjuntar el cuerpo de la petición si hay datos y el método lo requiere
        if (data && ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method.toUpperCase())) {
            options.body = JSON.stringify(data);
        }

        const response = await fetch(url, options);

        // Manejo de errores HTTP
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Ocurrió un error en la petición.');
        }
        // Si la respuesta es 204 No Content (por ejemplo, para eliminaciones exitosas sin retorno)
        if (response.status === 204) {
            return true;
        }
        // Intentar parsear la respuesta como JSON si el Content-Type lo indica
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.indexOf("application/json") !== -1) {
            return response.json();
        } else {
            // Si no es JSON, devolver la respuesta cruda (por ejemplo, si es HTML o texto plano)
            return response;
        }
    }

    /**
     * Carga y rellena el selector de fechas disponibles.
     */
    async function populateFechas() {
        try {
            const fechas = await fetchData('/administrador/fechas/disponibles');
            sessionFechaSelect.innerHTML = '<option value="" disabled selected>Seleccionar fecha</option>';
            fechas.forEach(fecha => {
                const option = document.createElement('option');
                option.value = fecha.id;
                // Formatear la fecha para mostrarla de forma legible
                const formattedDate = new Date(fecha.fecha).toLocaleDateString('es-ES', { weekday: 'short', day: 'numeric', month: 'numeric' });
                option.textContent = formattedDate;
                sessionFechaSelect.appendChild(option);
            });
        } catch (error) {
            console.error('Error al cargar las fechas:', error);
            showTemporaryMessage('Error al cargar las fechas: ' + error.message, 'error', 5000);
        }
    }

    /**
     * Carga y rellena el selector de salas disponibles.
     */
    async function populateSalas() {
        try {
            const salas = await fetchData('/administrador/salas');
            sessionSalaSelect.innerHTML = '<option value="" disabled selected>Seleccionar sala</option>';
            salas.forEach(sala => {
                const option = document.createElement('option');
                option.value = sala.id_sala;
                option.textContent = sala.text;
                sessionSalaSelect.appendChild(option);
            });
        } catch (error) {
            console.error('Error al cargar las salas:', error);
            showTemporaryMessage('Error al cargar las salas: ' + error.message, 'error', 5000);
        }
    }

    /**
     * Carga y rellena el selector de películas activas.
     */
    async function populatePeliculas() {
        try {
            const peliculas = await fetchData('/administrador/peliculas/activas-en-cartelera');
            sessionPeliculaSelect.innerHTML = '<option value="" disabled selected>Seleccionar película</option>';
            peliculas.forEach(pelicula => {
                const option = document.createElement('option');
                option.value = pelicula.id;
                option.textContent = pelicula.titulo;
                sessionPeliculaSelect.appendChild(option);
            });
        } catch (error) {
            console.error('Error al cargar las películas:', error);
            showTemporaryMessage('Error al cargar las películas: ' + error.message, 'error', 5000);
        }
    }

    /**
     * Carga y rellena el selector de horas disponibles para la combinación seleccionada de fecha, sala y película.
     */
    async function populateHorasDisponibles() {
        const fechaId = sessionFechaSelect.value;
        const salaId = sessionSalaSelect.value;
        const peliculaId = sessionPeliculaSelect.value;

        console.log('Valores actuales para horas:', { fecha: fechaId, sala: salaId, pelicula: peliculaId });

        sessionHoraSelect.innerHTML = '<option value="" disabled selected>Seleccionar hora</option>';
        sessionHoraSelect.disabled = true; // Desactivar por defecto

        // Solo intentar obtener horas si se han seleccionado los tres campos
        if (fechaId && salaId && peliculaId) {
            try {
                const url = `/administrador/sesiones/horas-disponibles?fecha_id=${fechaId}&sala_id=${salaId}&pelicula_id=${peliculaId}`;
                const horas = await fetchData(url);

                console.log('Horas recibidas del backend:', horas);

                if (horas.length === 0) {
                    sessionHoraSelect.innerHTML = '<option value="" disabled selected>No hay horas disponibles para esta combinación.</option>';
                } else {
                    horas.forEach(hora => {
                        const option = document.createElement('option');
                        option.value = hora.id; // Aquí 'hora.id' debería ser el valor que el backend envía, quizás 'hora.text'
                        option.textContent = hora.text;
                        sessionHoraSelect.appendChild(option);
                    });
                    sessionHoraSelect.disabled = false; // Habilitar el select de horas
                }

            } catch (error) {
                console.error('Error al obtener horas disponibles:', error);
                showTemporaryMessage('Error al cargar horas disponibles: ' + error.message, 'error', 5000);
                sessionHoraSelect.innerHTML = '<option value="" disabled selected>Error al cargar horas.</option>';
            }
        } else {
            sessionHoraSelect.innerHTML = '<option value="" disabled selected>Selecciona fecha, sala y película.</option>';
        }
    }

    /**
     * Carga y muestra las sesiones en la tabla para una fecha específica.
     * También actualiza el span de la fecha seleccionada.
     * @param {string|null} fechaId - El ID de la fecha para la que se quieren mostrar las sesiones.
     */
    async function fetchAndDisplaySessions(fechaId) {
        // Si no hay fechaId, ocultar la tabla y limpiar el mensaje de fecha
        if (!fechaId) {
            sessionsTable.style.display = 'none';
            noSessionsMessage.style.display = 'none';
            sessionsTableBody.innerHTML = '';
            if (selectedSessionDateSpan) {
                selectedSessionDateSpan.textContent = ''; // Limpiar la fecha mostrada
            }
            return;
        }

        // Obtener la fecha formateada de la opción seleccionada en el select de fechas
        const selectedOption = sessionFechaSelect.querySelector(`option[value="${fechaId}"]`);
        const formattedDateText = selectedOption ? selectedOption.textContent : 'Cargando...';
        
        // Actualizar el span con la fecha seleccionada al lado de "SESIONES CREADAS"
        if (selectedSessionDateSpan) {
            selectedSessionDateSpan.textContent = ` (${formattedDateText})`;
        }

        try {
            const sessions = await fetchData(`/administrador/sesiones-por-fecha/${fechaId}`);
    
            sessionsTableBody.innerHTML = ''; // Limpiar el cuerpo de la tabla

            if (sessions.length > 0) {
                sessions.forEach(session => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${session.id}</td>
                        <td>${session.pelicula_titulo}</td>
                        <td>${session.hora_inicio}</td>
                        <td>${session.hora_final}</td>
                        <td>${session.sala_nombre}</td>
                        <td>
                            <button class="delete-btn" data-session-id="${session.id}">Eliminar</button>
                        </td>
                    `;
                    sessionsTableBody.appendChild(row);
                });
                sessionsTable.style.display = 'table';
                noSessionsMessage.style.display = 'none';
            } else {
                sessionsTable.style.display = 'none';
                noSessionsMessage.style.display = 'block';
                noSessionsMessage.textContent = 'No hay sesiones creadas para esta fecha.';
            }
    
        } catch (error) {
            console.error('Error al cargar las sesiones para la tabla:', error);
            sessionsTable.style.display = 'none';
            noSessionsMessage.style.display = 'block';
            noSessionsMessage.textContent = `Error al cargar las sesiones: ${error.message}. Por favor, inténtalo de nuevo.`;
            if (selectedSessionDateSpan) {
                selectedSessionDateSpan.textContent = ''; // Limpiar la fecha en caso de error
            }
        }
    }

    // --- Event Listeners ---

    // Cuando cambia la fecha seleccionada, actualizar horas y recargar sesiones
    sessionFechaSelect.addEventListener('change', function() {
        populateHorasDisponibles();
        const selectedFechaId = this.value;
        fetchAndDisplaySessions(selectedFechaId);
    });

    // Cuando cambia la sala o la película, actualizar horas disponibles
    sessionSalaSelect.addEventListener('change', populateHorasDisponibles);
    sessionPeliculaSelect.addEventListener('change', populateHorasDisponibles);

    // Manejador de envío del formulario para crear una nueva sesión
    createSessionForm.addEventListener('submit', async function(event) {
        event.preventDefault(); // Prevenir el envío estándar del formulario

        // Mostrar mensaje de carga
        showTemporaryMessage('Creando sesión...', 'info', 0); // Sin duración para que sea sobrescrito

        const formData = {
            fecha: sessionFechaSelect.value,
            sala_id: sessionSalaSelect.value,
            pelicula_id: sessionPeliculaSelect.value,
            hora: sessionHoraSelect.value,
        };

        // Validar que todos los campos obligatorios estén completos
        if (!formData.fecha || !formData.sala_id || !formData.pelicula_id || !formData.hora) {
            showTemporaryMessage('Por favor, completa todos los campos obligatorios.', 'error', 5000);
            return; // Detener la ejecución si la validación falla
        }

        try {
            // Guardar el ID de la fecha seleccionada ANTES de resetear el formulario
            const currentFechaId = sessionFechaSelect.value; 

            // Realizar la petición para crear la sesión
            const response = await fetchData('/administrador/sesiones', 'POST', formData);

            // Mostrar mensaje de éxito
            showTemporaryMessage('Sesión creada y asientos creados exitosamente.', 'success', 3000);

            // Resetear el formulario y poblar las horas de nuevo (ahora estará vacío o con valores predeterminados)
            createSessionForm.reset();
            populateHorasDisponibles();

            // Usar el ID de la fecha guardado para recargar la tabla de sesiones
            if (currentFechaId) {
                fetchAndDisplaySessions(currentFechaId);
            }

        } catch (error) {
            console.error('Error al crear la sesión:', error);
            const errorMessage = error.message || 'Error al crear la sesión. Por favor, inténtalo de nuevo.';
            showTemporaryMessage(errorMessage, 'error', 5000); // Mostrar mensaje de error
        }
    });

    /**
     * Función para eliminar una sesión.
     * @param {number} sessionId - El ID de la sesión a eliminar.
     */
    async function deleteSession(sessionId) {
        try {
            const confirmation = confirm(`¿Estás seguro de que quieres eliminar la sesión ${sessionId}? Esta acción no se puede deshacer.`);
            if (!confirmation) {
                return; // Si el usuario cancela, no hacer nada
            }

            const response = await fetchData(`/administrador/sesiones/${sessionId}`, 'DELETE');

            if (response) {
                console.log(`Sesión ${sessionId} eliminada exitosamente.`, response);
                // Recargar la tabla de sesiones para la fecha actual
                if (sessionFechaSelect.value) {
                    fetchAndDisplaySessions(sessionFechaSelect.value);
                } else {
                    // Si por alguna razón no hay fecha seleccionada, limpiar la tabla
                    fetchAndDisplaySessions(); 
                }
                showTemporaryMessage('Sesión eliminada exitosamente.', 'success', 3000);
            } else {
                throw new Error('La eliminación de la sesión no retornó una respuesta exitosa.');
            }
        } catch (error) {
            console.error('Error al eliminar la sesión:', error);
            showTemporaryMessage(`Error al eliminar la sesión: ${error.message}`, 'error', 5000);
        }
    }

    // Event listener para los botones de eliminar en la tabla de sesiones (delegación de eventos)
    sessionsTableBody.addEventListener('click', function(event) {
        if (event.target.classList.contains('delete-btn')) {
            const sessionId = event.target.dataset.sessionId;
            deleteSession(sessionId);
        }
    });

    /**
     * Función para cargar los datos iniciales al cargar la página o sección.
     */
    function loadInitialData() {
        populateFechas();
        populateSalas();
        populatePeliculas();
        populateHorasDisponibles();
        // Un pequeño retraso para asegurar que el selector de fechas se ha poblado
        setTimeout(() => {
            if (sessionFechaSelect.value) {
                fetchAndDisplaySessions(sessionFechaSelect.value);
            }
        }, 100);
    }

    // Lógica para cargar datos cuando la sección es visible (si es un SPA)
    if (createSessionSection) {
        // Si la sección ya está visible al cargar la página, cargar datos
        if (!createSessionSection.classList.contains('hidden')) {
            loadInitialData();
            sessionDataLoaded = true;
        }

        // Escuchar un evento personalizado para cuando la sección se muestre
        createSessionSection.addEventListener('sectionShown', (event) => {
            if (event.detail.sectionId === 'create-session-section' && !sessionDataLoaded) {
                loadInitialData();
                sessionDataLoaded = true;
            }
        });

        // Escuchar un evento personalizado para cuando la sección se oculte
        createSessionSection.addEventListener('sectionHidden', (event) => {
            if (event.detail.sectionId === 'create-session-section') {
                sessionDataLoaded = false; // Marcar como no cargado si la sección se oculta
            }
        });
    } else {
        // Si no se usa un sistema de secciones (SPA), cargar los datos directamente
        loadInitialData();
    }
});