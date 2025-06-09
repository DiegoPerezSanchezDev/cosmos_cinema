document.addEventListener("DOMContentLoaded", function () {
    const modalRegistro = document.getElementById("modalRegistro");
    if (!modalRegistro) {
        console.log("[Registro] Modal de registro no encontrado. Saliendo.");
        return;
    }

    const checkUrlBase = "/check-";
    const passwordContainers = modalRegistro.querySelectorAll(".password-input-container");
    const mostrarRegistroBtn = document.getElementById("mostrarRegistro");
    const cerrarRegistroBtn = modalRegistro.querySelector("#cerrarRegistro");
    const form = modalRegistro.querySelector("form");
    const activeStep = modalRegistro.querySelector(".form-step.active");
    const clientSideErrorList = modalRegistro.querySelector(".client-side-errors ul");
    const clientSideErrorContainer = clientSideErrorList?.closest(".client-side-errors");

    let registroRecaptchaWidgetId = null;
    let registroRecaptchaRendered = false;

    function renderRegistroRecaptcha() {
        const container = document.getElementById('recaptcha-registro-container');

        if (!container) {
            console.error("[Registro] ERR: Contenedor 'recaptcha-registro-container' NO encontrado.");
            return;
        }

        if (typeof grecaptcha !== 'undefined' && grecaptcha.render) {
            if (!registroRecaptchaRendered) {
                if (!container.dataset.sitekey) {
                    console.error("[Registro] ERR: Sitekey está vacío o no definido en el data-attribute!");
                    return;
                }
                try {
                    registroRecaptchaWidgetId = grecaptcha.render('recaptcha-registro-container', {
                        'sitekey': container.dataset.sitekey,
                        'callback': function(response) {
                            console.log('[Registro] CBK: reCAPTCHA completado. Respuesta:', response);
                        },
                        'expired-callback': function() {
                            console.log('[Registro] CBK: reCAPTCHA expirado.');
                            if (registroRecaptchaWidgetId !== null) grecaptcha.reset(registroRecaptchaWidgetId);
                        },
                        'error-callback': function() {
                            console.error('[Registro] CBK_ERR: error-callback de reCAPTCHA disparado.');
                            registroRecaptchaRendered = false;
                            registroRecaptchaWidgetId = null;
                        }
                    });
                    if (typeof registroRecaptchaWidgetId === 'number') {
                        registroRecaptchaRendered = true;
                    } else {
                        console.warn("[Registro] WARN: grecaptcha.render no devolvió un widgetId numérico. Renderizado podría haber fallado. WidgetId:", registroRecaptchaWidgetId);
                    }
                } catch (e) {
                    console.error("[Registro] ERR_CATCH: Error en grecaptcha.render:", e);
                    registroRecaptchaRendered = false;
                }
            } else if (registroRecaptchaWidgetId !== null) {
                grecaptcha.reset(registroRecaptchaWidgetId);
            } else {
                registroRecaptchaRendered = false;
            }
        } else {
            console.error("[Registro] ERR: grecaptcha o grecaptcha.render no están disponibles.");
        }
    }

    passwordContainers.forEach((container) => {
        const passwordInput = container.querySelector('input[type="password"], input[type="text"]');
        const toggleIcon = container.querySelector(".toggle-password");
        if (passwordInput && toggleIcon) {
            toggleIcon.addEventListener("click", function () {
                const currentType = passwordInput.getAttribute("type");
                const newType = currentType === "password" ? "text" : "password";
                passwordInput.setAttribute("type", newType);
                toggleIcon.textContent = newType === "password" ? "👁️" : "🙈";
            });
        }
    });

    function openModal() {
        modalRegistro.classList.remove("hidden");
        modalRegistro.classList.add("flex");
        document.body.classList.add('modal_abierto');
        renderRegistroRecaptcha();
        const firstInput = activeStep?.querySelector('input:not([type="hidden"]), select, textarea');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 50);
        }
    }

    function closeModal() {
        modalRegistro.classList.remove("flex");
        modalRegistro.classList.add("hidden");
        document.body.classList.remove('modal_abierto');
        resetForm();
        if (typeof grecaptcha !== 'undefined' && grecaptcha.reset && registroRecaptchaWidgetId !== null) {
            grecaptcha.reset(registroRecaptchaWidgetId);
        }
        modalRegistro.querySelectorAll(".error-messages").forEach(el => {
            if (el.textContent.trim() !== '') {
                 el.style.display = "none";
            }
        });
    }

    if (cerrarRegistroBtn) cerrarRegistroBtn.addEventListener("click", closeModal);
    if (mostrarRegistroBtn) mostrarRegistroBtn.addEventListener("click", openModal);

    const cancelarRegistroBtn = modalRegistro.querySelector("#cancelarRegistroBtn");
    if (cancelarRegistroBtn) {
        cancelarRegistroBtn.addEventListener('click', closeModal);
    }

    function showGeneralClientErrors(messages) {
        if (!clientSideErrorList || !clientSideErrorContainer) return;
        clientSideErrorList.innerHTML = '';
        messages.forEach(msg => {
            const li = document.createElement('li');
            li.textContent = msg;
            clientSideErrorList.appendChild(li);
        });
        clientSideErrorContainer.style.display = messages.length > 0 ? "block" : "none";
    }

    function hideGeneralClientErrors() {
        if (!clientSideErrorList || !clientSideErrorContainer) return;
        clientSideErrorList.innerHTML = "";
        clientSideErrorContainer.style.display = "none";
    }

    function clearInvalidClassesFromInputs() {
        activeStep?.querySelectorAll("input.invalid, select.invalid").forEach(el => el.classList.remove("invalid"));
    }

    function resetForm() {
        if (form) form.reset();
        hideGeneralClientErrors();
        if (activeStep) clearStepFieldErrors(activeStep);
        clearInvalidClassesFromInputs();
        modalRegistro.querySelectorAll(".error-messages").forEach(el => el.style.display = "none");
    }

    function displayFieldError(inputElement, message) {
        if (!inputElement) return;
        const formRow = inputElement.closest(".form-row");
        if (formRow) {
            const errorElement = formRow.querySelector(".client-side-field-error");
            if (errorElement) {
                errorElement.textContent = message;
                errorElement.style.display = "block";
            }
            inputElement.classList.add("invalid");
        }
    }

    function clearFieldError(inputElement) {
        if (!inputElement) return;
        const formRow = inputElement.closest(".form-row");
        if (formRow) {
            const errorElement = formRow.querySelector(".client-side-field-error");
            if (errorElement) {
                errorElement.textContent = "";
                errorElement.style.display = "none";
            }
            inputElement.classList.remove("invalid");
        }
    }

    function clearStepFieldErrors(stepElement) {
        if (stepElement && typeof stepElement.querySelectorAll === 'function') {
            stepElement.querySelectorAll(".form-row .client-side-field-error").forEach((el) => {
                el.textContent = "";
                el.style.display = "none";
            });
        }
    }

    async function checkEmailExists(emailInput) {
        if (!emailInput || !emailInput.value.trim()) {
            clearFieldError(emailInput);
            return true;
        }
        const email = emailInput.value.trim();
        try {
            const response = await fetch(`${checkUrlBase}email?email=${encodeURIComponent(email)}`);
            if (!response.ok) {
                displayFieldError(emailInput, "Error al verificar email. Intenta de nuevo.");
                return false;
            }
            const data = await response.json();
            if (data.exists) {
                displayFieldError(emailInput, "Este email ya está registrado.");
                return false;
            } else {
                clearFieldError(emailInput);
                return true;
            }
        } catch (error) {
            console.error("[Registro] ERR: Error de conexión al verificar email:", error);
            displayFieldError(emailInput, "Error de conexión al verificar email.");
            return false;
        }
    }

    async function validateRegistroForm() {
        hideGeneralClientErrors();
        clearInvalidClassesFromInputs();
        if (activeStep) clearStepFieldErrors(activeStep);

        const emailInput = activeStep?.querySelector('[name="email"]');
        const emailConfirmationInput = activeStep?.querySelector('[name="email_confirmation"]');
        const passwordInput = activeStep?.querySelector('[name="password"]');
        const passwordConfirmationInput = activeStep?.querySelector('[name="password_confirmation"]');
        let isFormValid = true;

        if (!emailInput || !emailInput.value.trim()) {
            displayFieldError(emailInput, "El email es obligatorio.");
            isFormValid = false;
        } else if (!/\S+@\S+\.\S+/.test(emailInput.value)) {
            displayFieldError(emailInput, "Por favor, introduce un email válido.");
            isFormValid = false;
        } else {
            clearFieldError(emailInput);
        }

        if (!emailConfirmationInput || !emailConfirmationInput.value.trim()) {
            displayFieldError(emailConfirmationInput, "La confirmación del email es obligatoria.");
            isFormValid = false;
        } else if (emailInput && emailInput.value.trim() !== emailConfirmationInput.value.trim()) {
            displayFieldError(emailConfirmationInput, "El email y la confirmación no coinciden.");
            isFormValid = false;
        } else {
            clearFieldError(emailConfirmationInput);
        }

        // Comprobaciones de la contraseña
        if (!passwordInput || !passwordInput.value.trim()) {
            displayFieldError(passwordInput, "La contraseña es obligatoria.");
            isFormValid = false;
        } else if (passwordInput.value.length < 8) {
            displayFieldError(passwordInput, "La contraseña debe tener al menos 8 caracteres.");
            isFormValid = false;
        } else if (!/[A-Z]/.test(passwordInput.value)) {
            displayFieldError(passwordInput, "La contraseña debe contener al menos una letra mayúscula.");
            isFormValid = false;
        } else if (!/[^a-zA-Z0-9]/.test(passwordInput.value)) {
            displayFieldError(passwordInput, "La contraseña debe contener al menos un carácter especial.");
            isFormValid = false;
        } else {
            clearFieldError(passwordInput);
        }

        if (!passwordConfirmationInput || !passwordConfirmationInput.value) {
            displayFieldError(passwordConfirmationInput, "La confirmación de la contraseña es obligatoria.");
            isFormValid = false;
        } else if (passwordInput && passwordInput.value !== passwordConfirmationInput.value) {
            displayFieldError(passwordConfirmationInput, "La contraseña y la confirmación no coinciden.");
            isFormValid = false;
        } else {
            clearFieldError(passwordConfirmationInput);
        }

        if (isFormValid && emailInput && emailInput.value.trim() && /\S+@\S+\.\S+/.test(emailInput.value)) {
            const isEmailUnique = await checkEmailExists(emailInput);
            if (!isEmailUnique) {
                isFormValid = false;
            }
        }

        const recaptchaRegistroContainer = document.getElementById('recaptcha-registro-container');
        let recaptchaResponseRegistro = '';
        if (typeof grecaptcha !== 'undefined' && registroRecaptchaWidgetId !== null) {
            recaptchaResponseRegistro = grecaptcha.getResponse(registroRecaptchaWidgetId);
        } else {
            console.warn("[Registro] WARN: No se pudo obtener respuesta de reCAPTCHA (grecaptcha no definido o widgetId es null).");
        }

        if (recaptchaRegistroContainer && typeof grecaptcha !== 'undefined' && registroRecaptchaWidgetId !== null && recaptchaResponseRegistro.length === 0) {
            console.log("[Registro] VALIDATION_ERR: reCAPTCHA vacío.");
            showGeneralClientErrors(["Por favor, completa el reCAPTCHA."]);
            isFormValid = false;
        } else if (recaptchaRegistroContainer && (registroRecaptchaWidgetId === null || typeof grecaptcha === 'undefined') && registroRecaptchaRendered) {
             // Si se supone que está renderizado pero no tenemos ID o grecaptcha, es un problema.
            console.log("[Registro] VALIDATION_ERR: Problema con reCAPTCHA (renderizado pero sin ID/grecaptcha).");
            showGeneralClientErrors(["Hubo un problema con el reCAPTCHA. Inténtalo de nuevo."]);
            isFormValid = false;
        } else if (!recaptchaRegistroContainer && document.querySelector('.g-recaptcha')) {
            // Si el contenedor específico no está, pero hay algún div.g-recaptcha, podría ser un error de ID
            console.log("[Registro] VALIDATION_ERR: Contenedor reCAPTCHA específico no encontrado, pero existe un .g-recaptcha.");
            showGeneralClientErrors(["Error de configuración del reCAPTCHA."]);
            isFormValid = false;
        }
        
        return isFormValid;
    }

    if (form) {
        form.addEventListener("submit", async function (event) {
            event.preventDefault();
            hideGeneralClientErrors();
            clearInvalidClassesFromInputs();
            if (activeStep) clearStepFieldErrors(activeStep);

            const isFormValid = await validateRegistroForm();

            if (isFormValid) {
                event.target.submit();
            } else {
                console.log("[Registro] INF: Formulario NO válido.");
            }
        });
    }

    const backendErrorMessages = modalRegistro?.querySelectorAll("form .error-messages, form .error-text");
    let hasServerErrorsOnLoad = false;
    backendErrorMessages?.forEach(el => {
        if (el.textContent.trim() !== '') {
            hasServerErrorsOnLoad = true;
            el.style.display = "block";
        }
    });

    if (hasServerErrorsOnLoad) {
        if (modalRegistro && (modalRegistro.classList.contains("hidden") || !modalRegistro.classList.contains("flex"))) {
            openModal();
        }
    }

    if (modalRegistro) {
        document.addEventListener('keydown', function (event) {
            if ((event.key === 'Escape' || event.keyCode === 27) && modalRegistro.classList.contains('flex')) {
                closeModal();
            }
        });
        modalRegistro.addEventListener('click', function (event) {
            if (event.target === modalRegistro && modalRegistro.classList.contains('flex')) {
                closeModal();
            }
        });
    }
});