import moment from 'moment';
import 'moment/locale/es';

document.addEventListener("DOMContentLoaded", function () {
    const modalCuenta = document.getElementById("modalCuenta");
    if (!modalCuenta) {
        return;
    }

    // El modal Mi Cuenta se cierra al clicar fuera del modal
    if (modalCuenta) {
        modalCuenta.addEventListener('click', function (event) {
            if (event.target === modalCuenta && modalCuenta.classList.contains('flex')) {
                closeCuentaModal();
            }
        });
    }

    // El modal Mi Cuenta se cierra al presionar 'Escape'
    if (modalCuenta) {
        document.addEventListener('keydown', function (event) {
            if ((event.key === 'Escape' || event.keyCode === 27) && modalCuenta.classList.contains('flex')) {

                event.preventDefault();
                closeCuentaModal();
            }
        });
    }

    const cerrarCuentaModalBtn = modalCuenta?.querySelector("#cerrarCuentaModal");
    if (cerrarCuentaModalBtn) {
        cerrarCuentaModalBtn.addEventListener('click', closeCuentaModal);
    }



    const modalPeliculaCartelera = document.getElementById("modal_detalle");
    const cerrarPeliculaCarteleraModalBtn = modalPeliculaCartelera?.querySelector("#cerrarPeliculaCarteleraModal");
    if (cerrarPeliculaCarteleraModalBtn) {
        cerrarPeliculaCarteleraModalBtn.addEventListener('click', () => {
            modalPeliculaCartelera.classList.remove('flex');
            modalPeliculaCartelera.classList.add('hidden');
            document.body.classList.remove('modal_abierto');
        });
    }

    const mostrarCuentaModalBtn = document.getElementById("miCuenta");
    const editarPerfilBtn = modalCuenta.querySelector("#editarPerfilBtn");
    const guardarCambiosBtn = modalCuenta.querySelector("#guardarCambiosBtn");
    const cancelarEdicionBtn = modalCuenta.querySelector("#cancelarEdicionBtn");
    const cuentaModalTitle = modalCuenta.querySelector("#cuentaModalTitle");

    const infoNombreDisplay = modalCuenta.querySelector("#infoNombreDisplay");
    const infoNombreEdit = modalCuenta.querySelector("#infoNombreEdit");
    const infoNombreError = modalCuenta.querySelector("#infoNombreError");
    const formRowNombre = infoNombreEdit?.closest(".form-row");

    const infoApellidosDisplay = modalCuenta.querySelector("#infoApellidosDisplay");
    const infoApellidosEdit = modalCuenta.querySelector("#infoApellidosEdit");
    const infoApellidosError = modalCuenta.querySelector("#infoApellidosError");
    const formRowApellidos = infoApellidosEdit?.closest(".form-row");

    const infoEmailDisplay = modalCuenta.querySelector("#infoEmailDisplay");
    const infoEmailEdit = modalCuenta.querySelector("#infoEmailEdit");

    const infoFechaNacimientoDisplay = modalCuenta.querySelector("#infoFechaNacimientoDisplay");
    const infoFechaNacimientoEdit = modalCuenta.querySelector("#infoFechaNacimientoEdit");
    const infoFechaNacimientoError = modalCuenta.querySelector("#infoFechaNacimientoError");
    const formRowFechaNacimiento = infoFechaNacimientoEdit?.closest(".form-row");

    const infoTelefonoDisplay = modalCuenta.querySelector("#infoTelefonoDisplay");
    const infoTelefonoEdit = modalCuenta.querySelector("#infoTelefonoEdit");
    const infoTelefonoError = modalCuenta.querySelector("#infoTelefonoError");
    const formRowTelefono = infoTelefonoEdit?.closest(".form-row");

    const infoDniDisplay = modalCuenta.querySelector("#infoDniDisplay");
    const infoDniEdit = modalCuenta.querySelector("#infoDniEdit");
    const infoDniError = modalCuenta.querySelector("#infoDniError");
    const formRowDni = infoDniEdit?.closest(".form-row");

    const infoDireccionDisplay = modalCuenta.querySelector("#infoDireccionDisplay");
    const infoDireccionEdit = modalCuenta.querySelector("#infoDireccionEdit");
    const infoDireccionError = modalCuenta.querySelector("#infoDireccionError");
    const formRowDireccion = infoDireccionEdit?.closest(".form-row");

    const infoCiudadDisplay = modalCuenta.querySelector("#infoCiudadDisplay");
    const infoCiudadEdit = modalCuenta.querySelector("#infoCiudadEdit");
    const infoCiudadError = modalCuenta.querySelector("#infoCiudadError");
    const formRowCiudad = infoCiudadEdit?.closest(".form-row");

    const infoCodigoPostalDisplay = modalCuenta.querySelector("#infoCodigoPostalDisplay");
    const infoCodigoPostalEdit = modalCuenta.querySelector("#infoCodigoPostalEdit");
    const infoCodigoPostalError = modalCuenta.querySelector("#infoCodigoPostalError");
    const formRowCodigoPostal = infoCodigoPostalEdit?.closest(".form-row");

    const infoMayorEdadConfirmadoEdit = modalCuenta.querySelector("#infoMayorEdadConfirmadoEdit");
    const infoMayorEdadConfirmadoError = modalCuenta.querySelector("#infoMayorEdadConfirmadoError");
    const formRowMayorEdadConfirmado = infoMayorEdadConfirmadoEdit?.closest(".form-row");
    const serverSideErrorsArea = modalCuenta.querySelector(".client-side-errors ul");
    const serverSideErrorsContainer = modalCuenta.querySelector(".client-side-errors");
    const profileUpdateSuccessMessage = modalCuenta.querySelector("#profileUpdateSuccessMessage");
    const infoAceptaTerminosEdit = modalCuenta.querySelector("#infoAceptaTerminosEdit");
    const formRowAceptaTerminos = infoAceptaTerminosEdit?.closest(".form-row");
    const infoAceptaTerminosError = modalCuenta.querySelector("#infoAceptaTerminosEditError");

    let currentUserData = null;
    let isCompletingProfileGlobal = false;

    function openCuentaModal(forceCompleteMode = false) {
        if (modalCuenta) {
            loadAndPopulateUserData(forceCompleteMode);
        }
    }

    function closeCuentaModal() {
        if (modalCuenta) {
            modalCuenta.classList.remove("flex");
            modalCuenta.classList.add("hidden");
            document.body.classList.remove('modal_abierto');
            switchToDisplayMode();
            clearAllClientErrors();
        }
    }

    function switchToEditMode(isCompleting = false) {
        isCompletingProfileGlobal = isCompleting;
        if (!modalCuenta || !currentUserData) return;

        modalCuenta.classList.add("is-editing");
        if(editarPerfilBtn) editarPerfilBtn.style.display = "none";
        if(guardarCambiosBtn) guardarCambiosBtn.style.display = "inline-block";
        if(cancelarEdicionBtn) cancelarEdicionBtn.style.display = "inline-block";

        modalCuenta.querySelectorAll(".display-field").forEach((el) => (el.style.display = "none"));
        modalCuenta.querySelectorAll(".edit-field-container").forEach((el) => (el.style.display = "block"));

        if(infoNombreEdit) infoNombreEdit.disabled = false;
        if(infoApellidosEdit) infoApellidosEdit.disabled = false;
        if(infoTelefonoEdit) infoTelefonoEdit.disabled = false;
        if(infoDireccionEdit) infoDireccionEdit.disabled = false;
        if(infoCiudadEdit) infoCiudadEdit.disabled = false;
        if(infoCodigoPostalEdit) infoCodigoPostalEdit.disabled = false;
        if(infoAceptaTerminosEdit) infoAceptaTerminosEdit.disabled = false;
        if(infoEmailEdit) infoEmailEdit.disabled = true;
        if(infoMayorEdadConfirmadoEdit) infoMayorEdadConfirmadoEdit.disabled = false;
        if (infoFechaNacimientoEdit) infoFechaNacimientoEdit.disabled = !(isCompleting || !currentUserData.fecha_nacimiento);
        if (infoDniEdit) infoDniEdit.disabled = !(isCompleting || !currentUserData.dni);

        const ID_TIPO_CLIENTE = 3; // Asegúrate que este sea el ID correcto para "cliente"
        const dniFieldContainer = formRowDni; // Asumiendo que formRowDni es el contenedor del campo DNI

        if (dniFieldContainer) {
            if (currentUserData && currentUserData.tipo_usuario_id == ID_TIPO_CLIENTE) {
                dniFieldContainer.style.display = 'none';
                if(infoDniEdit) infoDniEdit.disabled = true;
            } else {
                dniFieldContainer.style.display = 'flex'; // O 'block' según tu CSS para .form-row
                 // La lógica anterior de habilitar/deshabilitar basado en isCompleting o si tiene valor ya se aplica
            }
        }


        if (isCompleting) {
            if(cuentaModalTitle) cuentaModalTitle.textContent = "Completa tu Perfil";
        } else {
            if(cuentaModalTitle) cuentaModalTitle.textContent = "Editar Mi Cuenta";
        }
        clearAllClientErrors();
        if(profileUpdateSuccessMessage) profileUpdateSuccessMessage.style.display = "none";
    }

    function switchToDisplayMode() {
        isCompletingProfileGlobal = false;
        if (!modalCuenta) return;

        modalCuenta.classList.remove("is-editing");
        if(editarPerfilBtn) editarPerfilBtn.style.display = "inline-block";
        if(guardarCambiosBtn) guardarCambiosBtn.style.display = "none";
        if(cancelarEdicionBtn) cancelarEdicionBtn.style.display = "none";
        if(cuentaModalTitle) cuentaModalTitle.textContent = "Mi Cuenta";

        modalCuenta.querySelectorAll(".display-field").forEach((el) => (el.style.display = "inline-block"));
        modalCuenta.querySelectorAll(".edit-field-container").forEach((el) => (el.style.display = "none"));
        clearAllClientErrors();
    }

    function clearAllClientErrors() {
        modalCuenta?.querySelectorAll(".client-side-field-error").forEach((errorEl) => {
            const formRow = errorEl.closest(".form-row");
            clearFieldError(errorEl, formRow);
            const input = formRow?.querySelector("input, select");
            if (input) input.classList.remove("invalid");
        });
        if (serverSideErrorsContainer) serverSideErrorsContainer.style.display = "none";
        if (serverSideErrorsArea) serverSideErrorsArea.innerHTML = "";
    }

    async function populateCitiesSelect(selectElement, selectedCityId = null) {
        if (!selectElement) return;
        selectElement.innerHTML = '<option value="">Cargando ciudades...</option>';
        selectElement.disabled = true;
        try {
            const response = await fetch("/ciudades", {
                method: "GET",
                headers: { Accept: "application/json" },
            });
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const cities = await response.json();
            selectElement.innerHTML = '<option value="">Selecciona una ciudad</option>';
            if (Array.isArray(cities)) {
                cities.forEach((city) => {
                    const option = document.createElement("option");
                    option.value = city.id;
                    option.textContent = city.nombre;
                    if (selectedCityId && city.id == selectedCityId) {
                        option.selected = true;
                    }
                    selectElement.appendChild(option);
                });
            }
            selectElement.disabled = false;
        } catch (error) {
            console.error("Error en fetch para obtener ciudades:", error);
            selectElement.innerHTML = '<option value="">Error al cargar ciudades</option>';
            selectElement.disabled = false;
        }
    }

    function populateFormFields(userData) {
        if (!userData) return;

        if (infoNombreDisplay) infoNombreDisplay.textContent = userData.nombre || "No especificado";
        if (infoApellidosDisplay) infoApellidosDisplay.textContent = userData.apellidos || "No especificado";
        if (infoEmailDisplay) infoEmailDisplay.textContent = userData.email || "No especificado";
        if (infoFechaNacimientoDisplay) infoFechaNacimientoDisplay.textContent = userData.fecha_nacimiento ? moment(userData.fecha_nacimiento, "YYYY-MM-DD").format("DD/MM/YYYY") : "No especificado";
        if (infoTelefonoDisplay) infoTelefonoDisplay.textContent = userData.numero_telefono || "No especificado";
        if (infoDniDisplay) infoDniDisplay.textContent = userData.dni || "No especificado";
        if (infoDireccionDisplay) infoDireccionDisplay.textContent = userData.direccion || "No especificado";
        if (infoCiudadDisplay) infoCiudadDisplay.textContent = userData.ciudad_nombre || "No especificado";
        if (infoCodigoPostalDisplay) infoCodigoPostalDisplay.textContent = userData.codigo_postal || "No especificado";

        if (infoNombreEdit) infoNombreEdit.value = userData.nombre || "";
        if (infoApellidosEdit) infoApellidosEdit.value = userData.apellidos || "";
        if (infoEmailEdit) infoEmailEdit.value = userData.email || "";
        if (infoFechaNacimientoEdit) infoFechaNacimientoEdit.value = userData.fecha_nacimiento || "";
        if (infoTelefonoEdit) infoTelefonoEdit.value = userData.numero_telefono || "";
        if (infoDniEdit) infoDniEdit.value = userData.dni || "";
        if (infoDireccionEdit) infoDireccionEdit.value = userData.direccion || "";
        if (infoCodigoPostalEdit) infoCodigoPostalEdit.value = userData.codigo_postal || "";

        if (infoMayorEdadConfirmadoEdit) infoMayorEdadConfirmadoEdit.checked = !!userData.mayor_edad_confirmado;
        const infoAceptaTerminosEdit = modalCuenta.querySelector("#infoAceptaTerminosEdit"); // Asegúrate de tener este ID en tu HTML
        if (infoAceptaTerminosEdit) infoAceptaTerminosEdit.checked = !!userData.acepta_terminos;

        if (infoCiudadEdit && userData.ciudad_id) {
            infoCiudadEdit.value = userData.ciudad_id;
        }
    }

    async function loadAndPopulateUserData(forceCompleteMode = false) {
        if(profileUpdateSuccessMessage) profileUpdateSuccessMessage.style.display = "none";
        clearAllClientErrors();
        try {
            const response = await fetch("/perfil/datos", {
                method: "GET",
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                },
            });
            if (!response.ok) {
    
                const errorData = await response.json().catch(() => ({ message: "Error desconocido al cargar datos." }));
                console.error('Error al cargar datos del usuario:', response.status, response.statusText, errorData.message);
                // No usar alert(), usar un modal personalizado si es necesario
                // alert(errorData.message || "No se pudieron cargar los datos de tu cuenta. Intenta de nuevo.");
                closeCuentaModal();
                return;
            }
            currentUserData = await response.json(); // PUNTO 1 DE FALLO POTENCIAL
            console.log('Datos JSON recibidos de /perfil/datos:', currentUserData);
    
            if (!currentUserData || typeof currentUserData !== 'object') { // PUNTO 2
                console.error("No se recibieron datos válidos del usuario o no es un objeto:", currentUserData);
                throw new Error("Formato de datos de usuario inesperado."); // Esto activaría el catch
            }
    
            populateFormFields(currentUserData); // PUNTO 3
            await populateCitiesSelect(infoCiudadEdit, currentUserData.ciudad_id); // PUNTO 4
    
            const ID_TIPO_CLIENTE = 3; // Asegúrate que este sea el ID correcto para "cliente"
            const dniFieldContainer = formRowDni;
            if (dniFieldContainer) {
                if (currentUserData.tipo_usuario_id == ID_TIPO_CLIENTE) {
                    dniFieldContainer.style.display = 'none';
                    if(infoDniEdit) infoDniEdit.disabled = true;
                } else {
                    dniFieldContainer.style.display = 'flex'; // O 'block' según tu CSS para .form-row
                    // La lógica de habilitar/deshabilitar en switchToEditMode se encargará de si el input DNI es editable o no
                }
            }
    
            // Modificación aquí: Siempre se inicia en modo de visualización.
            // La lógica para forzar el modo de edición si el perfil está incompleto
            // se maneja en la redirección del controlador de login social,
            // pero el modal en sí siempre abre en display mode inicialmente.
            switchToDisplayMode();
    
            // Si el perfil está incompleto, se puede mostrar un mensaje o una indicación visual
            // pero el modal se abre en modo display.
            if (currentUserData && !currentUserData.is_profile_complete) {
                // Opcional: Mostrar un mensaje en el modal para que el usuario sepa que su perfil está incompleto
                if(profileUpdateSuccessMessage) { // Reutilizamos el elemento de mensaje de éxito para esto
                    profileUpdateSuccessMessage.textContent = "¡Bienvenido/a! Tu perfil está incompleto. Haz clic en 'Editar Perfil' para completarlo.";
                    profileUpdateSuccessMessage.style.display = "block";
                    profileUpdateSuccessMessage.style.color = "orange"; // Para distinguirlo
                }
            }
    
    
            modalCuenta.classList.remove("hidden");
            modalCuenta.classList.add("flex");
            document.body.classList.add('modal_abierto');
        } catch (error) {
            console.error("Error al cargar y popular datos del usuario:", error);
            // No usar alert(), usar un modal personalizado si es necesario
            // alert("Ocurrió un error al cargar tus datos. Intenta de nuevo más tarde.");
            closeCuentaModal();
        }
    }

    if (mostrarCuentaModalBtn) {
        mostrarCuentaModalBtn.addEventListener("click", function (event) {
            event.preventDefault();
            openCuentaModal(false);
        });
    }

    if (editarPerfilBtn) {
        editarPerfilBtn.addEventListener("click", () => switchToEditMode(false));
    }

    if (cancelarEdicionBtn) {
        cancelarEdicionBtn.addEventListener("click", () => {
            if (isCompletingProfileGlobal && !currentUserData?.is_profile_complete) {
                closeCuentaModal();
            } else {
                if(currentUserData) populateFormFields(currentUserData);
                switchToDisplayMode();
            }
        });
    }

    function calculateDniLetter(dniNumber) {
        const letras = "TRWAGMYFPDXBNJZSQVHLCKE";
        const numberStr = String(dniNumber).trim();
        const number = parseInt(numberStr, 10);
        if (isNaN(number) || numberStr.length !== 8) {
            return null;
        }
        return letras[number % 23];
    }

    function isValidDniFormatAndLetter(dniValue) {
        if (!dniValue || dniValue.trim() === '') return { valid: true, message: "" };
        const dniRegex = /^\d{8}[A-Za-z]$/;
        if (!dniRegex.test(dniValue)) {
            return { valid: false, message: "Formato: 8 números y 1 letra." };
        }
        const numberPart = dniValue.substring(0, 8);
        const letterPart = dniValue.substring(8).toUpperCase();
        const calculatedLetter = calculateDniLetter(numberPart);
        if (calculatedLetter !== null && calculatedLetter === letterPart) {
            return { valid: true, message: "" };
        } else {
            return { valid: false, message: "La letra del DNI no es correcta." };
        }
    }

    async function checkDniIsUniqueProfile(dniInput) {
        if (!dniInput || !dniInput.value.trim() || dniInput.disabled) {
            clearFieldError(infoDniError, formRowDni);
            return true;
        }
        const dni = dniInput.value.trim().toUpperCase();
        try {
            const response = await fetch(`/check-dni-profile?dni=${encodeURIComponent(dni)}`);
            if (!response.ok) {
                displayFieldError(infoDniError, formRowDni, "Error al verificar DNI.");
                return false;
            }
            const data = await response.json();
            if (data.exists) {
                displayFieldError(infoDniError, formRowDni, "Este DNI ya está registrado.");
                return false;
            }
            clearFieldError(infoDniError, formRowDni);
            return true;
        } catch (error) {
            displayFieldError(infoDniError, formRowDni, "Error de conexión al verificar DNI.");
            return false;
        }
    }

    function isValidBirthDateAndAge(dateInput) {
        if (!dateInput || dateInput.disabled) {
            clearFieldError(infoFechaNacimientoError, formRowFechaNacimiento);
            return true;
        }
        if (!dateInput.value.trim()){
            if (isCompletingProfileGlobal || !currentUserData?.fecha_nacimiento) {
                displayFieldError(infoFechaNacimientoError, formRowFechaNacimiento, "La fecha de nacimiento es obligatoria.");
                return false;
            }
            clearFieldError(infoFechaNacimientoError, formRowFechaNacimiento);
            return true;
        }
        const birthDateStr = dateInput.value;
        const birthDate = new Date(birthDateStr);
        if (isNaN(birthDate.getTime())) {
            displayFieldError(infoFechaNacimientoError, formRowFechaNacimiento, "Fecha de nacimiento inválida.");
            return false;
        }
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        if (age < 14) {
            displayFieldError(infoFechaNacimientoError, formRowFechaNacimiento, "Debes tener al menos 14 años.");
            return false;
        }
        clearFieldError(infoFechaNacimientoError, formRowFechaNacimiento);
        return true;
    }

    if (guardarCambiosBtn) {
        guardarCambiosBtn.addEventListener("click", async function (event) {
            event.preventDefault();
            clearAllClientErrors();
            if(profileUpdateSuccessMessage) profileUpdateSuccessMessage.style.display = "none";

            let isClientValid = true;

            if (infoNombreEdit && infoNombreEdit.value.trim() !== '' && !validateNoNumbers(infoNombreEdit.value, infoNombreError, formRowNombre, "El nombre solo debe contener letras y espacios.")) isClientValid = false;
            else if (infoNombreEdit) clearFieldError(infoNombreError, formRowNombre);

            if (infoApellidosEdit && infoApellidosEdit.value.trim() !== '' && !validateNoNumbers(infoApellidosEdit.value, infoApellidosError, formRowApellidos, "Los apellidos solo deben contener letras y espacios.")) isClientValid = false;
            else if (infoApellidosEdit) clearFieldError(infoApellidosError, formRowApellidos);

            if (infoTelefonoEdit && infoTelefonoEdit.value.trim() !== '' && !validateNineDigitsNumeric(infoTelefonoEdit.value, infoTelefonoError, formRowTelefono, "El teléfono debe tener 9 números.")) isClientValid = false;
            else if (infoTelefonoEdit && infoTelefonoEdit.value.trim() === '' && infoTelefonoEdit.hasAttribute('required')) {
                displayFieldError(infoTelefonoError, formRowTelefono, "El teléfono es obligatorio."); isClientValid = false;
            } else if (infoTelefonoEdit) clearFieldError(infoTelefonoError, formRowTelefono);

            if (infoCodigoPostalEdit && infoCodigoPostalEdit.value.trim() !== '' && !validateFiveDigitsNumeric(infoCodigoPostalEdit.value, infoCodigoPostalError, formRowCodigoPostal, "El código postal debe tener 5 números.")) isClientValid = false;
            else if (infoCodigoPostalEdit && infoCodigoPostalEdit.value.trim() === '' && infoCodigoPostalEdit.hasAttribute('required')) {
                displayFieldError(infoCodigoPostalError, formRowCodigoPostal, "El código postal es obligatorio."); isClientValid = false;
            } else if (infoCodigoPostalEdit) clearFieldError(infoCodigoPostalError, formRowCodigoPostal);

            if (infoDireccionEdit && infoDireccionEdit.value.trim() === '' && infoDireccionEdit.hasAttribute('required')) {
                displayFieldError(infoDireccionError, formRowDireccion, "La dirección es obligatoria."); isClientValid = false;
            } else if (infoDireccionEdit) clearFieldError(infoDireccionError, formRowDireccion);

            if (infoFechaNacimientoEdit && !infoFechaNacimientoEdit.disabled) {
                if (!isValidBirthDateAndAge(infoFechaNacimientoEdit)) {
                    isClientValid = false;
                }
            }

            if (document.querySelector('#infoAceptaTerminosEdit') && !document.querySelector('#infoAceptaTerminosEdit').checked) {
                displayFieldError(null, document.querySelector('#infoAceptaTerminosEdit').closest('.form-row'), "Debes aceptar los Términos y Condiciones.");
                isClientValid = false;
            }


            if (infoCiudadEdit) {
                if (infoCiudadEdit.value === '' && (isCompletingProfileGlobal || !currentUserData?.ciudad_id)) {
                    displayFieldError(infoCiudadError, formRowCiudad, "Debes seleccionar una ciudad.");
                    isClientValid = false;
                } else {
                    clearFieldError(infoCiudadError, formRowCiudad);
                }
            }

            if (infoMayorEdadConfirmadoEdit && !infoMayorEdadConfirmadoEdit.disabled) {
                if (!infoMayorEdadConfirmadoEdit.checked && (isCompletingProfileGlobal || !currentUserData?.mayor_edad_confirmado)) {
                    displayFieldError(infoMayorEdadConfirmadoError, formRowMayorEdadConfirmado, "Debes confirmar que eres mayor de 14 años.");
                    isClientValid = false;
                } else {
                    clearFieldError(infoMayorEdadConfirmadoError, formRowMayorEdadConfirmado);
                }
            }

            if (infoAceptaTerminosEdit && !infoAceptaTerminosEdit.disabled) {
                if (!infoAceptaTerminosEdit.checked && (isCompletingProfileGlobal || !currentUserData?.acepta_terminos)) {
                    displayFieldError(infoAceptaTerminosError, formRowAceptaTerminos, "Debes aceptar los Términos y Condiciones.");
                    isClientValid = false;
                } else {
                    clearFieldError(infoAceptaTerminosError, formRowAceptaTerminos);
                }
            }

            if (!isClientValid) return;

            const payload = {
                nombre: infoNombreEdit?.value.trim() || null,
                apellidos: infoApellidosEdit?.value.trim() || null,
                numero_telefono: infoTelefonoEdit?.value.trim() || null,
                direccion: infoDireccionEdit?.value.trim() || null,
                ciudad_id: infoCiudadEdit?.value || null,
                codigo_postal: infoCodigoPostalEdit?.value.trim() || null,
                fecha_nacimiento: (infoFechaNacimientoEdit && !infoFechaNacimientoEdit.disabled && infoFechaNacimientoEdit.value) ? infoFechaNacimientoEdit.value : undefined,
                dni: (infoDniEdit && !infoDniEdit.disabled && infoDniEdit.value && dniFieldContainer && dniFieldContainer.style.display !== 'none') ? infoDniEdit.value.trim().toUpperCase() : undefined,
                mayor_edad_confirmado: infoMayorEdadConfirmadoEdit?.checked ?? false,
                acepta_terminos: infoAceptaTerminosEdit?.checked ?? false,
            };
            const filteredPayload = Object.fromEntries(Object.entries(payload).filter(([_, v]) => v !== undefined));

            try {
                const response = await fetch("/perfil/modificar", {
                    method: "PATCH",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                    },
                    body: JSON.stringify(filteredPayload),
                });
                const responseData = await response.json();
                if (response.ok) {
                    currentUserData = responseData.user;
                    populateFormFields(currentUserData);
                    if (infoCiudadEdit && currentUserData.ciudad_id) {
                        infoCiudadEdit.value = currentUserData.ciudad_id;
                    }
                    if(profileUpdateSuccessMessage) {
                        profileUpdateSuccessMessage.textContent = responseData.message || "Perfil actualizado.";
                        profileUpdateSuccessMessage.style.display = "block";
                    }
                    setTimeout(() => {
                        if(profileUpdateSuccessMessage) profileUpdateSuccessMessage.style.display = "none";
                        closeCuentaModal();
                        window.location.href = "/";
                    }, 1500);
                } else if (response.status === 422) {
                    if (responseData.errors) {
                        clearAllClientErrors();
                        for (const field in responseData.errors) {
                            const errorMessages = responseData.errors[field];
                            let errorElement = null; let formRow = null;
                            if (field === "nombre") { errorElement = infoNombreError; formRow = formRowNombre; }
                            else if (field === "apellidos") { errorElement = infoApellidosError; formRow = formRowApellidos; }
                            else if (field === "numero_telefono") { errorElement = infoTelefonoError; formRow = formRowTelefono; }
                            else if (field === "direccion") { errorElement = infoDireccionError; formRow = formRowDireccion; }
                            else if (field === "ciudad_id") { errorElement = infoCiudadError; formRow = formRowCiudad; }
                            else if (field === "codigo_postal") { errorElement = infoCodigoPostalError; formRow = formRowCodigoPostal; }
                            else if (field === "fecha_nacimiento") { errorElement = infoFechaNacimientoError; formRow = formRowFechaNacimiento; }
                            else if (field === "dni") { errorElement = infoDniError; formRow = formRowDni; }
                            else if (field === "mayor_edad_confirmado") { errorElement = infoMayorEdadConfirmadoError; formRow = formRowMayorEdadConfirmado; }
                            else if (field === "acepta_terminos") { errorElement = infoAceptaTerminosError; formRow = formRowAceptaTerminos; }
                            if (errorElement) {
                                displayFieldError(errorElement, formRow, errorMessages.join(" "));
                            } else {
                                if (serverSideErrorsArea && serverSideErrorsContainer) {
                                    const li = document.createElement("li");
                                    li.textContent = `${field}: ${errorMessages.join(" ")}`;
                                    serverSideErrorsArea.appendChild(li);
                                    serverSideErrorsContainer.style.display = "block";
                                }
                            }
                        }
                    }
                } else {
                    if (serverSideErrorsArea && serverSideErrorsContainer) {
                        serverSideErrorsArea.innerHTML = `<li>${responseData.message || "Error al guardar."}</li>`;
                        serverSideErrorsContainer.style.display = "block";
                    }
                }
            } catch (error) {
                if (serverSideErrorsArea && serverSideErrorsContainer) {
                    serverSideErrorsArea.innerHTML = "<li>Error de conexión. Inténtalo de nuevo.</li>";
                    serverSideErrorsContainer.style.display = "block";
                }
            }
        });
    }

    if (cerrarCuentaModalBtn) {
        cerrarCuentaModalBtn.addEventListener("click", closeCuentaModal);
    }

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get("complete_profile") === "true" && mostrarCuentaModalBtn) {
        openCuentaModal(true);
    }
    if (typeof showProfileModalCompletaPerfil !== "undefined" && showProfileModalCompletaPerfil && mostrarCuentaModalBtn) {
       openCuentaModal(true);
    }

    function validateNoNumbers(value, errorElement, formRowElement, errorMessage) {
        const regex = /^[a-zA-ZÀ-ÖØ-öø-ÿ\s\.\-]*$/;
        if (!value || value.trim() === '') {
            if (formRowElement?.querySelector("input, select")?.hasAttribute('required') ||
                (isCompletingProfileGlobal && (formRowElement === formRowNombre || formRowElement === formRowApellidos) && !currentUserData?.nombre) ) {
                displayFieldError(errorElement, formRowElement, "Este campo es obligatorio."); return false;
            }
            clearFieldError(errorElement, formRowElement); return true;
        }
        if (!regex.test(value)) {
            displayFieldError(errorElement, formRowElement, errorMessage); return false;
        }
        clearFieldError(errorElement, formRowElement); return true;
    }

    function validateFiveDigitsNumeric(value, errorElement, formRowElement, errorMessage) {
        const regex = /^\d{5}$/;
        const cleanedValue = value ? value.replace(/\s/g, '') : '';
        if (!cleanedValue) {
             if (formRowElement?.querySelector("input, select")?.hasAttribute('required') ||
                (isCompletingProfileGlobal && formRowElement === formRowCodigoPostal && !currentUserData?.codigo_postal) ) {
                displayFieldError(errorElement, formRowElement, "Este campo es obligatorio."); return false;
            }
            clearFieldError(errorElement, formRowElement); return true;
        }
        if (!regex.test(cleanedValue)) {
            displayFieldError(errorElement, formRowElement, errorMessage); return false;
        }
        clearFieldError(errorElement, formRowElement); return true;
    }

    function validateNineDigitsNumeric(value, errorElement, formRowElement, errorMessage) {
        const regex = /^\d{9}$/;
        const cleanedValue = value ? value.replace(/\s/g, '') : '';
         if (!cleanedValue) {
             if (formRowElement?.querySelector("input, select")?.hasAttribute('required') ||
                (isCompletingProfileGlobal && formRowElement === formRowTelefono && !currentUserData?.numero_telefono) ) {
                displayFieldError(errorElement, formRowElement, "Este campo es obligatorio."); return false;
            }
            clearFieldError(errorElement, formRowElement); return true;
        }
        if (!regex.test(cleanedValue)) {
            displayFieldError(errorElement, formRowElement, errorMessage); return false;
        }
        clearFieldError(errorElement, formRowElement); return true;
    }

    function displayFieldError(errorElement, formRowElement, message) {
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = "block";
            const inputElement = formRowElement?.querySelector("input, select");
            if (inputElement) inputElement.classList.add("invalid");
        }
    }

    function clearFieldError(errorElement, formRowElement) {
        if (errorElement) {
            errorElement.textContent = "";
            errorElement.style.display = "none";
            const inputElement = formRowElement?.querySelector("input, select");
            if (inputElement) inputElement.classList.remove("invalid");
        }
    }
});