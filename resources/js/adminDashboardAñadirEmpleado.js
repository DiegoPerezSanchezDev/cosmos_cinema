document.addEventListener("DOMContentLoaded", () => {
    const userForm = document.getElementById('add-user-form');
    const userCreationMessageArea = document.getElementById('user-creation-message');
    let messageTimeoutId = null;

    function showTemporaryMessage(message, type = 'success', duration = 3000) {
        
        if (messageTimeoutId) {
            clearTimeout(messageTimeoutId);
            messageTimeoutId = null;
        }
        userCreationMessageArea.textContent = message;
        userCreationMessageArea.style.color = type === 'success' ? 'green' : (type === 'error' ? 'red' : 'orange');
        userCreationMessageArea.style.fontWeight = 'bold';
        userCreationMessageArea.style.display = 'block';
        userCreationMessageArea.style.opacity = '1';
        if (duration > 0) {
            messageTimeoutId = setTimeout(() => {
                userCreationMessageArea.textContent = '';
                userCreationMessageArea.style.color = '';
                userCreationMessageArea.style.fontWeight = '';
                userCreationMessageArea.style.display = 'none';
                userCreationMessageArea.style.opacity = '0';
                messageTimeoutId = null;
            }, duration);
        }
    }

    function displayFieldError(inputElement, message) {
        const formGroup = inputElement ? inputElement.closest(".mb-3") : null;
        if (formGroup) {
            const errorElement = formGroup.querySelector(
                ".client-side-field-error"
            );
            if (errorElement) {
                errorElement.innerHTML = message;
                errorElement.style.display = "block";
                if (inputElement && inputElement.classList) inputElement.classList.add('is-invalid');
            }
        } else if (inputElement) {
            const errorElement = inputElement.nextElementSibling;
            if (errorElement && errorElement.classList.contains('client-side-field-error')) {
                errorElement.innerHTML = message;
                errorElement.style.display = "block";
                if (inputElement && inputElement.classList) inputElement.classList.add('is-invalid');
            }
        }
    }

    function clearFieldError(inputElement) {
        const formGroup = inputElement ? inputElement.closest(".mb-3") : null;
        if (formGroup) {
            const errorElement = formGroup.querySelector(
                ".client-side-field-error"
            );
            if (errorElement) {
                errorElement.innerHTML = "";
                errorElement.style.display = "none";
                if (inputElement && inputElement.classList) inputElement.classList.remove('is-invalid');
            }
        } else if (inputElement) {
            const errorElement = inputElement.nextElementSibling;
            if (errorElement && errorElement.classList.contains('client-side-field-error')) {
                errorElement.innerHTML = "";
                errorElement.style.display = "none";
                if (inputElement && inputElement.classList) inputElement.classList.remove('is-invalid');
            }
        }
    }

    function clearAllFieldErrors() {
        userForm.querySelectorAll(".client-side-field-error").forEach((el) => {
            el.innerHTML = "";
            el.style.display = "none";
        });
        userForm.querySelectorAll('.form-control, .form-select, input[type="checkbox"]').forEach(el => el.classList.remove('is-invalid'));
    }

    function isValidDniLetter(dni) {
        const dniFormatRegex = /^\d{8}[A-Za-z]$/;
        if (!dni || !dniFormatRegex.test(dni)) {
            return false;
        }
        const number = parseInt(dni.substr(0, 8), 10);
        const letter = dni.substr(8, 1).toUpperCase();
        const validLetters = 'TRWAGMYFPDXBNJZSQVHLCKE';
        const expectedLetter = validLetters[number % 23];
        return letter === expectedLetter;
    }

    async function checkDniExists(dniInput) {
        const dni = dniInput ? dniInput.value.trim() : "";
        const dniFormatRegex = /^\d{8}[A-Za-z]$/;
        if (!dni || !dniFormatRegex.test(dni)) {
            clearFieldError(dniInput);
            return false;
        }
        const uniqueErrorSpan = dniInput.closest(".mb-3")?.querySelector(".client-side-field-error");
        if (uniqueErrorSpan && uniqueErrorSpan.innerHTML.includes('existe ese DNI')) {
            clearFieldError(dniInput);
        }
        try {
            const response = await fetch(
                `/administrador/check-dni?dni=${encodeURIComponent(dni)}`
            );
            if (!response.ok) {
                return false;
            }
            const data = await response.json();
            if (data.exists) {
                return false;
            } else {
                clearFieldError(dniInput);
                return true;
            }
        } catch (error) {
            return false;
        }
    }

    async function validateAdminUserForm() {
        clearAllFieldErrors();
        let isFormValid = true;
        const nombreInput = userForm.querySelector('[name="nombre"]');
        const apellidosInput = userForm.querySelector('[name="apellidos"]');
        const direccionInput = userForm.querySelector('[name="direccion"]');
        const ciudadSelect = userForm.querySelector('[name="ciudad"]');
        const codigoPostalInput = userForm.querySelector('[name="codigo_postal"]');
        const numeroTelefonoInput = userForm.querySelector('[name="numero_telefono"]');
        const dniInput = userForm.querySelector('[name="dni"]');
        const fechaNacimientoInput = userForm.querySelector('[name="fecha_nacimiento"]');
        const tipoUsuarioSelect = userForm.querySelector('[name="tipo_usuario"]');
        if (!nombreInput || !nombreInput.value.trim()) {
            displayFieldError(nombreInput, "El nombre es obligatorio.");
            isFormValid = false;
        } else {
            const nameRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/;
            if (!nameRegex.test(nombreInput.value.trim())) {
                displayFieldError(nombreInput, "El nombre solo puede contener letras y espacios.");
                isFormValid = false;
            } else if (nombreInput.value.trim().length > 100) {
                displayFieldError(nombreInput, "El nombre no debe exceder los 100 caracteres.");
                isFormValid = false;
            } else {
                clearFieldError(nombreInput);
            }
        }
        if (!apellidosInput || !apellidosInput.value.trim()) {
            displayFieldError(
                apellidosInput,
                "Los apellidos son obligatorios."
            );
            isFormValid = false;
        } else {
            const apellidosRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/;
            if (!apellidosRegex.test(apellidosInput.value.trim())) {
                displayFieldError(apellidosInput, "Los apellidos solo pueden contener letras y espacios.");
                isFormValid = false;
            } else if (apellidosInput.value.trim().length > 100) {
                displayFieldError(apellidosInput, "El apellidos no debe exceder los 100 caracteres.");
                isFormValid = false;
            } else {
                clearFieldError(apellidosInput);
            }
        }
        if (!direccionInput || !direccionInput.value.trim()) {
            displayFieldError(direccionInput, "La Dirección es obligatoria.");
            isFormValid = false;
        } else {
            const direccionRegex = /^[a-zA-Z0-9\s\/\-#.,]*$/;
            if (!direccionRegex.test(direccionInput.value.trim())) {
                displayFieldError(
                    direccionInput,
                    "Formato de dirección no válido."
                );
                isFormValid = false;
            } else if (direccionInput.value.trim().length > 255) {
                displayFieldError(direccionInput, "La dirección no debe exceder los 255 caracteres.");
                isFormValid = false;
            } else {
                clearFieldError(direccionInput);
            }
        }
        if (!ciudadSelect || !ciudadSelect.value) {
            displayFieldError(ciudadSelect, "Debes seleccionar una ciudad.");
            isFormValid = false;
        } else {
            clearFieldError(ciudadSelect);
        }
        if (!codigoPostalInput || !codigoPostalInput.value.trim()) {
            displayFieldError(
                codigoPostalInput,
                "El Código Postal es obligatorio."
            );
            isFormValid = false;
        } else {
            const cpRegex = /^\d{5}$/;
            if (!cpRegex.test(codigoPostalInput.value.trim())) {
                displayFieldError(
                    codigoPostalInput,
                    "El C.P debe tener 5 dígitos numéricos."
                );
                isFormValid = false;
            } else {
                clearFieldError(codigoPostalInput);
            }
        }
        if (!numeroTelefonoInput || !numeroTelefonoInput.value.trim()) {
            displayFieldError(
                numeroTelefonoInput,
                "El teléfono es obligatorio."
            );
            isFormValid = false;
        } else {
            const phoneRegex = /^\d{9}$/;
            if (!phoneRegex.test(numeroTelefonoInput.value.trim())) {
                displayFieldError(
                    numeroTelefonoInput,
                    "El teléfono debe tener exactamente 9 dígitos numéricos."
                );
                isFormValid = false;
            } else {
                clearFieldError(numeroTelefonoInput);
            }
        }
        let dniFormatValid = true;
        const dniValue = dniInput ? dniInput.value.trim() : "";
        if (!dniInput || !dniValue) {
            displayFieldError(dniInput, "El DNI es obligatorio.");
            isFormValid = false;
            dniFormatValid = false;
        } else {
            const dniFormatRegex = /^\d{8}[A-Za-z]$/;
            if (!dniFormatRegex.test(dniValue)) {
                displayFieldError(
                    dniInput,
                    "El formato del DNI debe ser 8 números seguidos de una letra."
                );
                isFormValid = false;
                dniFormatValid = false;
            } else if (!isValidDniLetter(dniValue)) {
                displayFieldError(
                    dniInput,
                    "La letra del DNI no se corresponde con los números."
                );
                isFormValid = false;
                dniFormatValid = false;
            } else {
                clearFieldError(dniInput);
            }
        }
        if (dniFormatValid) {
            const isDniUnique = await checkDniExists(dniInput);
            if (!isDniUnique) {
                isFormValid = false;
            }
        }
        if (!fechaNacimientoInput || !fechaNacimientoInput.value.trim()) {
            displayFieldError(
                fechaNacimientoInput,
                "La Fecha de Nacimiento es obligatoria."
            );
            isFormValid = false;
        } else {
            const birthDate = new Date(fechaNacimientoInput.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const requiredAge = 14;
            const ageLimitDate = new Date(today.getFullYear() - requiredAge, today.getMonth(), today.getDate());
            if (isNaN(birthDate.getTime())) {
                displayFieldError(
                    fechaNacimientoInput,
                    "Introduce una fecha de nacimiento válida."
                );
                isFormValid = false;
            } else if (birthDate >= today) {
                displayFieldError(
                    fechaNacimientoInput,
                    "La fecha de nacimiento debe ser anterior a hoy."
                );
                isFormValid = false;
            }
            else if (birthDate > ageLimitDate) {
                displayFieldError(
                    fechaNacimientoInput,
                    `El usuario debe ser mayor de ${requiredAge} años.`
                );
                isFormValid = false;
            }
            else {
                clearFieldError(fechaNacimientoInput);
            }
        }
        if (!tipoUsuarioSelect || !tipoUsuarioSelect.value) {
            displayFieldError(
                tipoUsuarioSelect,
                "Debes seleccionar el tipo de usuario."
            );
            isFormValid = false;
        } else {
            clearFieldError(tipoUsuarioSelect);
        }
        return isFormValid;
    }
    userForm.addEventListener("submit", async function (event) {
        event.preventDefault();
        hideTemporaryMessageArea();
        showTemporaryMessage('Validando y enviando...', 'info', 0);
        const formData = new FormData(userForm);
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const response = await fetch(userForm.action, {
                method: userForm.method,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            if (response.ok) {
                const result = await response.json();
                showTemporaryMessage(result.message || 'Usuario creado exitosamente.', 'success', 5000);
                userForm.reset();
                clearAllFieldErrors();
                 // Redirect to dashboard after success
                if (typeof adminDashboardUrl !== 'undefined') {
                    setTimeout(() => {
                        showTemporaryMessage("Empleado generado Correctamente");
                    }, 5000); // Match message duration
                }
            } else {
                const errorData = await response.json();
                console.error('Backend error response:', errorData);
                if (response.status === 422 && errorData.errors) {
                    let generalErrorMessage = 'Por favor, corrige los siguientes errores:';
                    let hasSpecificErrors = false;
                    for (const field in errorData.errors) {
                        const messages = errorData.errors[field];
                        const inputElement = userForm.querySelector(`[name="${field}"]`);
                        if (inputElement) {
                            displayFieldError(inputElement, messages.join(' '));
                            hasSpecificErrors = true;
                        } else {
                            generalErrorMessage += ` ${field}: ${messages.join(' ')};`;
                        }
                    }
                    if (hasSpecificErrors) {
                        showTemporaryMessage('Hay errores en el formulario. Revisa los campos marcados.', 'error', 5000);
                    } else {
                        showTemporaryMessage(generalErrorMessage, 'error', 5000);
                    }
                } else {
                    const errorMessage = errorData.message || 'Ocurrió un error en el servidor.';
                    showTemporaryMessage(errorMessage, 'error', 5000);
                }
            }
        } catch (error) {
            console.error('Error en la petición fetch o procesando respuesta:', error);
            showTemporaryMessage('Error de conexión. No se pudo comunicar con el servidor.', 'error', 5000);
        }
    });
    function hideTemporaryMessageArea() {
        if (userCreationMessageArea) {
            userCreationMessageArea.textContent = '';
            userCreationMessageArea.style.color = '';
            userCreationMessageArea.style.fontWeight = '';
            userCreationMessageArea.style.display = 'none';
            userCreationMessageArea.style.opacity = '0';
            if (messageTimeoutId) {
                clearTimeout(messageTimeoutId);
                messageTimeoutId = null;
            }
        }
    }
});