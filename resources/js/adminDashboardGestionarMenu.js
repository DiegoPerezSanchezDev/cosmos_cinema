document.addEventListener('DOMContentLoaded', () => {
    // --- SELECTORES DE ELEMENTOS ---
    const manageMenuSection = document.getElementById('manage-menu-section');
    const addNewMenuItemButton = document.getElementById('add-new-menu-item-button');
    const menuSearchInput = document.getElementById('menu-search-input');
    const menuStatusSelect = document.getElementById('menu-status-select');
    const menuItemsPerPageSelect = document.getElementById(
        "menu-items-per-page-select"
    );
    const menuFilterButton = document.getElementById('menu-filter-button');
    const manageMenuArea = document.querySelector('.manage-menu-area');

    const menuPrevPageBtn = document.getElementById('menu-prev-page-btn');
    const menuNextPageBtn = document.getElementById('menu-next-page-btn');
    const menuPageInfo = document.getElementById('menu-page-info');

    const menuItemModal = document.getElementById('menu-item-modal');
    const menuItemModalTitle = document.getElementById('menu-item-modal-title');
    const closeButton = menuItemModal.querySelector('.close-button');
    const menuItemForm = document.getElementById('menu-item-form');
    const menuItemIdInput = document.getElementById('menu-item-id');
    const menuItemNombreInput = document.getElementById('menu-item-nombre');
    const menuItemDescripcionInput = document.getElementById('menu-item-descripcion');
    const menuItemPrecioInput = document.getElementById('menu-item-precio');
    const menuItemFotoInput = document.getElementById('menu-item-foto');
    const menuItemFotoPreview = document.getElementById('menu-item-foto-preview');
    const menuItemCurrentFotoRutaInput = document.getElementById('menu-item-current-foto-ruta');
    const saveMenuItemButton = document.getElementById('save-menu-item-button');
    const cancelMenuItemButton = document.getElementById('cancel-menu-item-button');
    const gestionarMenuCosmosLink = document.getElementById('gestionar-menu-cosmos-link');

    // --- NUEVOS SELECTORES PARA MENSAJES DE FORMULARIO ---
    const menuFormMessageArea = document.getElementById('menu-form-message');
    let menuFormMessageTimeoutId = null;

    // --- ESTADO ---
    let currentPage = 1;
    let totalPages = 1;
    let totalItems = 0;
    let isEditMode = false;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const defaultImagePath = '/images/menus/imagenDefecto.jpeg'; // Ruta a tu imagen por defecto en public
    let menuItemsLoaded = false;

    // Listener para el enlace de gestionar menú (si existe y tiene esa función)
    if (gestionarMenuCosmosLink && menuFilterButton) {
        gestionarMenuCosmosLink.addEventListener('click', (event) => {
            event.preventDefault(); // Evita la navegación del enlace si es un <a>
            fetchMenuItems(1); // Llama directamente a la función para cargar los menús
        });
    }

    // --- FUNCIONES DE UI ---
    // Muestra mensajes en el área principal de gestión de menú
    const displayMenuMessage = (message) => {
        manageMenuArea.innerHTML = `<p>${message}</p>`;
        updatePaginationControls({ current_page: 0, last_page: 0, total: 0 });
    };

    // Muestra errores en el área principal de gestión de menú
    const displayMenuError = (message = "Ocurrió un error al cargar los elementos del menú.") => {
        manageMenuArea.innerHTML = `<p style="color: red;">${message}</p>`;
        updatePaginationControls({ current_page: 0, last_page: 0, total: 0 });
    };

    // Función para construir el HTML de un elemento de menú
    const buildMenuItemHtml = (item) => {
        const imageUrl = item.imagen_url || defaultImagePath;
        const formattedPrice = item.precio !== null ? `${parseFloat(item.precio).toFixed(2)} €` : 'N/A';
        const statusButtonText = item.activo ? 'Desactivar' : 'Activar';
        const statusButtonClass = item.activo ? 'btn-toggle-status deactivate' : 'btn-toggle-status activate';

        // La variable imageName parece no usarse en el retorno, pero la dejo si la necesitas
        let imageName = '';
        if (item.imagen_url) {
            const parts = item.imagen_url.split('/');
            imageName = parts[parts.length - 1];
        } else {
            const parts = defaultImagePath.split('/');
            imageName = parts[parts.length - 1];
        }

        return `
            <div class="managed-menu-item">
                <img src="${imageUrl}" alt="${item.nombre}" style="width: 100px; height: auto; border-radius: 4px;">
                <div class="menu-item-details">
                    <h4>${item.nombre || 'N/A'}</h4>
                    <p>Precio: ${formattedPrice}</p>
                    <p class='sinopsis'>${item.descripcion ? item.descripcion.substring(0, 100) + '...' : 'Sin descripción'}</p>
                    <p>Estado: <span class="status-text ${item.activo ? 'status-active' : 'status-inactive'}">${item.activo ? 'Activo' : 'Inactivo'}</span></p>
                </div>
                <div class="menu-item-actions">
                    <button class="${statusButtonClass}" data-item-id="${item.id}">${statusButtonText}</button>
                    <button class="btn-edit-item" data-item-id="${item.id}">Editar</button>
                </div>
            </div>
        `;
    };

    // Renderiza los ítems del menú en el área de gestión
    function renderMenuItems(items) {
        manageMenuArea.innerHTML = ''; // Limpia el área
        if (!items || items.length === 0) {
            displayMenuMessage('No se encontraron elementos del menú.');
            return;
        }
        items.forEach(item => {
            manageMenuArea.innerHTML += buildMenuItemHtml(item); // Añade el HTML de cada ítem
        });

        // Adjunta event listeners a los botones dinámicamente creados
        const toggleStatusButtons = manageMenuArea.querySelectorAll('.btn-toggle-status');
        toggleStatusButtons.forEach(button => {
            button.addEventListener('click', () => toggleItemStatus(button.dataset.itemId));
        });

        const editButtons = manageMenuArea.querySelectorAll('.btn-edit-item');
        editButtons.forEach(button => {
            button.addEventListener('click', () => openModalForEdit(button.dataset.itemId));
        });
    }

    // Actualiza los controles de paginación
    function updatePaginationControls(paginationData) {
        const current = paginationData.current_page ?? 0;
        const last = paginationData.last_page ?? 0;
        const total = paginationData.total ?? 0;

        menuPageInfo.textContent = total > 0 ? `Página ${current} de ${last} (${total} elementos)` : `Página 0 de 0 (0 elementos)`;
        menuPrevPageBtn.disabled = current <= 1;
        menuNextPageBtn.disabled = current >= last || last === 0;

        totalPages = last;
        totalItems = total;
        currentPage = current;
    }

    // Carga elementos del menú desde el backend con filtros y paginación
    async function fetchMenuItems(page = 1) {
        const search = menuSearchInput.value.trim();
        const status = menuStatusSelect.value;
        const perPage = parseInt(menuItemsPerPageSelect.value);

        const params = new URLSearchParams({
            page: page,
            perPage: perPage,
        });
        if (search) params.append('search', search);
        if (status !== 'all') params.append('status', status === 'active' ? 1 : 0);

        displayMenuMessage('Cargando elementos del menú...');
        menuFilterButton.disabled = true;
        menuPrevPageBtn.disabled = true;
        menuNextPageBtn.disabled = true;

        try {
            // Nota: Asegúrate de que esta URL sea correcta en tu entorno (ej. /administrador/menu)
            const response = await fetch(`/administrador/menu?${params.toString()}`, {
                method: 'GET',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: 'Error de red' }));
                throw new Error(errorData.message || `Error ${response.status}`);
            }

            const data = await response.json();

            if (data && typeof data.data !== 'undefined') {
                renderMenuItems(data.data);
                updatePaginationControls(data);
            } else if (Array.isArray(data)) {
                renderMenuItems(data);
                updatePaginationControls({
                    current_page: 1,
                    last_page: 1,
                    total: data.length,
                });
                console.warn("Respuesta no paginada detectada.");
            } else {
                renderMenuItems([]);
                updatePaginationControls({ current_page: 0, last_page: 0, total: 0 });
                console.warn("Respuesta inesperada del backend.");
            }
            menuItemsLoaded = true;

        } catch (error) {
            console.error('Error al cargar elementos del menú:', error);
            displayMenuError(`Error al cargar elementos: ${error.message}`);
            updatePaginationControls({ current_page: 0, last_page: 0, total: 0 });
        } finally {
            menuFilterButton.disabled = false;
            menuPrevPageBtn.disabled = false;
            menuNextPageBtn.disabled = false;
        }
    }

    // Abre el modal para añadir un nuevo elemento
    function openModalForAdd() {
        isEditMode = false;
        menuItemForm.reset(); // Limpia el formulario
        menuItemIdInput.value = ''; // Borra el ID del ítem
        menuItemCurrentFotoRutaInput.value = ''; // Borra la ruta de foto actual
        menuItemModalTitle.textContent = 'Añadir Elemento al Menú'; // Cambia el título
        saveMenuItemButton.textContent = 'Añadir Elemento'; // Cambia el texto del botón de guardar
        menuItemFotoPreview.style.display = 'none'; // Oculta la previsualización de la imagen
        menuItemFotoPreview.src = '#'; // Reinicia la src de la previsualización
        menuItemFotoInput.style.display = 'block'; // Asegura que el input de archivo sea visible en modo añadir
        menuItemModal.style.display = 'flex'; // Muestra el modal
        menuItemNombreInput.focus(); // Enfoca el primer campo
        document.body.classList.add('modal-open'); // Añadir clase al body para evitar scroll
        showMessage('', 'info', 0); // Limpia mensajes anteriores en el modal
    }

    // Abre el modal para editar un elemento existente
    async function openModalForEdit(itemId) {
        isEditMode = true;
        menuItemForm.reset();
        menuItemModalTitle.textContent = 'Cargando...';
        menuItemModal.style.display = 'flex';
        document.body.classList.add('modal-open'); // Añadir clase al abrir
        showMessage('', 'info', 0); // Limpia mensajes anteriores en el modal

        try {
            const response = await fetch(`/administrador/menu/${itemId}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            });
            if (!response.ok) throw new Error('No se pudo cargar el elemento.');

            const item = await response.json();
            menuItemModalTitle.textContent = 'Editar Elemento del Menú';
            saveMenuItemButton.textContent = 'Guardar Cambios';

            // Rellena el formulario con los datos del ítem
            menuItemIdInput.value = item.id;
            menuItemNombreInput.value = item.nombre || '';
            menuItemDescripcionInput.value = item.descripcion || '';
            menuItemPrecioInput.value = item.precio !== null ? parseFloat(item.precio).toFixed(2) : '';
            menuItemCurrentFotoRutaInput.value = item.imagen_url || '';

            // Muestra la imagen actual o la por defecto en la previsualización
            if (item.imagen_url) {
                menuItemFotoPreview.src = item.imagen_url;
                menuItemFotoPreview.style.display = 'block';
            } else {
                menuItemFotoPreview.src = defaultImagePath; // Mostrar default si no hay imagen_url
                menuItemFotoPreview.style.display = 'block';
            }
            menuItemFotoInput.style.display = 'none';
        } catch (error) {
            showMessage(`Error al cargar para editar: ${error.message}`, 'error', 5000);
            closeModal();
        }
    }

    // Cierra el modal y limpia el body
    function closeModal() {
        menuItemModal.style.display = 'none';
        menuItemForm.reset();
        document.body.classList.remove('modal-open'); // Remover clase al cerrar
        showMessage('', 'info', 0); // Limpia mensajes al cerrar
    }

    // Maneja el envío del formulario del modal (añadir/editar)
    async function handleFormSubmit(event) {
        event.preventDefault(); // Evita el envío tradicional del formulario
        saveMenuItemButton.disabled = true;
        saveMenuItemButton.textContent = isEditMode ? 'Guardando...' : 'Añadiendo...';
        showMessage('Procesando...', 'info', 0); // Muestra mensaje de procesamiento

        const formData = new FormData(menuItemForm); // Crea un objeto FormData para enviar datos (incluyendo archivos)
        const itemId = menuItemIdInput.value;
        let url = '/administrador/menu';
        let method = 'POST'; // FormData con POST maneja archivos

        // Si es modo edición, ajusta la URL y añade el método PUT simulado
        if (isEditMode && itemId) {
            url = `/administrador/menu/${itemId}`;
            formData.append('_method', 'PUT'); // Laravel usa _method para simular PUT con POST
        }

        try {
            const response = await fetch(url, {
                method: method,
                body: formData,
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken } // Headers necesarios
            });

            const responseData = await response.json(); // Parsea la respuesta del servidor

            if (!response.ok) {
                // Si la respuesta no es OK (ej. 422, 500)
                if (response.status === 422 && responseData.errors) {
                    // Errores de validación (status 422)
                    let errorMessages = "Errores de validación:\n";
                    for (const field in responseData.errors) {
                        errorMessages += `- ${responseData.errors[field].join(', ')}\n`;
                    }
                    showMessage(errorMessages, 'error', 5000); // Muestra los errores de validación
                } else {
                    // Otros errores del servidor
                    throw new Error(responseData.message || `Error ${response.status}`);
                }
            } else {
                // Operación exitosa
                // Mostramos el mensaje de éxito sin auto-borrado
                showMessage(responseData.message || 'Operación exitosa.', 'success', 0); // No auto-clear

                // Introducimos un pequeño retraso antes de cerrar el modal y recargar la lista
                setTimeout(() => {
                    closeModal(); // Esto limpiará el mensaje como parte de su rutina normal
                    fetchMenuItems(); // Recarga la lista de elementos del menú
                }, 1500); // Esperar 1.5 segundos (1500 ms) antes de cerrar y recargar
            }
        } catch (error) {
            // Errores de red o inesperados
            showMessage(`Error al guardar: ${error.message}`, 'error', 5000);
        } finally {
            saveMenuItemButton.disabled = false; // Vuelve a habilitar el botón
            saveMenuItemButton.textContent = isEditMode ? 'Guardar Cambios' : 'Añadir Elemento'; // Restaura el texto del botón
        }
    }

    // Cambia el estado activo/inactivo de un ítem del menú
    async function toggleItemStatus(itemId) {
        const itemDiv = manageMenuArea.querySelector(`.managed-menu-item [data-item-id="${itemId}"]`);
        const button = itemDiv?.closest('.managed-menu-item')?.querySelector('.btn-toggle-status');
        if (!button) return; // Si no encuentra el botón, sale

        const originalButtonText = button.textContent;
        button.disabled = true;
        button.textContent = "Cambiando..."; // Muestra estado de "cambiando"

        try {
            const response = await fetch(`menu/${itemId}/estadoActivo`, {
                method: 'PATCH', // Usa PATCH para actualizar el estado
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            });

            const result = await response.json(); // Parsea la respuesta JSON

            if (response.ok) {
                console.log(result.message); // Log de mensaje de éxito
                // Actualiza el texto y las clases del botón
                button.textContent = result.new_status ? 'Desactivar' : 'Activar';
                button.classList.remove('activate', 'deactivate');
                button.classList.add(result.new_status ? 'deactivate' : 'activate');

                // Actualiza el span de estado visual (Activo/Inactivo)
                const statusSpan = button.closest('.managed-menu-item')?.querySelector('.status-active, .status-inactive');
                if (statusSpan) {
                    statusSpan.textContent = result.new_status ? 'Activo' : 'Inactivo';
                    statusSpan.classList.remove('status-active', 'status-inactive');
                    statusSpan.classList.add(result.new_status ? 'status-active' : 'status-inactive');
                }
                // Muestra un mensaje de éxito general en el área del formulario (ya que es para todo el dashboard)
                showMessage(result.message || 'Estado actualizado con éxito.', 'success', 3000);
            } else {
                // Manejo de errores si la respuesta no es OK
                const errorMessage = result.error || `Error al cambiar estado (Estado ${response.status}).`;
                console.error('Error response from backend:', result);
                showMessage('Error: ' + errorMessage, 'error', 5000); // Muestra el error en el div
                button.textContent = originalButtonText; // Restaura el texto del botón
            }
        } catch (error) {
            // Manejo de errores de red o inesperados
            console.error('Error al cambiar estado del menú:', error);
            showMessage('Error al intentar cambiar el estado: ' + error.message, 'error', 5000); // Muestra el error en el div
            button.textContent = originalButtonText; // Restaura el texto del botón
        } finally {
            button.disabled = false; // Vuelve a habilitar el botón
        }
    }

    // Muestra la vista previa de la imagen seleccionada en el formulario
    function previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                menuItemFotoPreview.src = e.target.result;
                menuItemFotoPreview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            // Si se deselecciona, mostrar la actual (si es edición) o la default
            const currentPath = menuItemCurrentFotoRutaInput.value;
            menuItemFotoPreview.src = (isEditMode && currentPath) ? currentPath : defaultImagePath;
            menuItemFotoPreview.style.display = (isEditMode && currentPath) || !isEditMode ? 'block' : 'none';
            if (!isEditMode && !file) { // Si es modo añadir y no hay archivo, ocultar preview
                menuItemFotoPreview.style.display = 'none';
                menuItemFotoPreview.src = '#';
            }
        }
    }

    // Función para mostrar mensajes en el área específica del formulario
    // Reemplaza los alert() y console.log de la función anterior
    function showMessage(message, type = 'success', duration = 3000) {
        if (!menuFormMessageArea) {
            console.warn('Elemento #menu-form-message no encontrado para mostrar mensajes. Fallback a alert.');
            alert(message); // Fallback a alert si el elemento no existe (no debería pasar con la instrucción de HTML)
            return;
        }

        if (menuFormMessageTimeoutId) {
            clearTimeout(menuFormMessageTimeoutId);
            menuFormMessageTimeoutId = null;
        }

        menuFormMessageArea.textContent = message;
        menuFormMessageArea.style.color = type === 'success' ? 'green' : (type === 'error' ? 'red' : 'orange');
        menuFormMessageArea.style.display = 'block';
        menuFormMessageArea.style.opacity = '1'; // Asegura que sea visible

        if (duration > 0) {
            menuFormMessageTimeoutId = setTimeout(() => {
                menuFormMessageArea.textContent = '';
                menuFormMessageArea.style.color = '';
                menuFormMessageArea.style.display = 'none';
                menuFormMessageArea.style.opacity = '0'; // Transición suave si se usa CSS
                menuFormMessageTimeoutId = null;
            }, duration);
        }
    }


    // Inicialización de la sección
    function init() {
        // Asegúrate de que el modal esté oculto al inicio
        menuItemModal.style.display = 'none';
        // Limpiar el manageMenuArea inicialmente con un mensaje de carga
        manageMenuArea.innerHTML = '<p>Cargando elementos del menú...</p>';

        // Adjunta event listeners a los botones y selectores
        addNewMenuItemButton.addEventListener('click', openModalForAdd);
        menuFilterButton.addEventListener('click', () => { fetchMenuItems(1);});

        // Listeners para filtros dinámicos (keypress, change)
        menuSearchInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') fetchMenuItems(1); });
        menuStatusSelect.addEventListener('change', () => fetchMenuItems(1));
        menuItemsPerPageSelect.addEventListener('change', () => fetchMenuItems(1));

        // Listeners para botones de paginación
        menuPrevPageBtn.addEventListener('click', () => { if (currentPage > 1) fetchMenuItems(currentPage - 1); });
        menuNextPageBtn.addEventListener('click', () => { if (currentPage < totalPages) fetchMenuItems(currentPage + 1); });

        // Listeners para cerrar el modal y enviar el formulario
        closeButton.addEventListener('click', closeModal);
        cancelMenuItemButton.addEventListener('click', closeModal);
        // menuItemModal.addEventListener('click', (event) => { if (event.target === menuItemModal) closeModal(); }); // Esta línea sigue comentada
        menuItemForm.addEventListener('submit', handleFormSubmit);
        menuItemFotoInput.addEventListener('change', previewImage);

        // Cargar datos iniciales si la sección está visible (por ejemplo, al cargar la página del dashboard)
        if (manageMenuSection && !manageMenuSection.classList.contains('hidden')) {
            fetchMenuItems();
        }
    }

    // Función para ser llamada cuando la sección se active (si es necesario en tu sistema de tabs/secciones)
    window.activateManageMenuSection = () => {
        if (manageMenuSection && !manageMenuSection.classList.contains('hidden') && !menuItemsLoaded) {
            fetchMenuItems();
        }
    };

    // Inicializar la funcionalidad al cargar el DOM
    init();
});
