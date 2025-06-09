document.addEventListener("DOMContentLoaded", () => {

    const emailInput = document.getElementById("email-input");
    const adminCodeContainer = document.getElementById("admin-code-container");
    const adminCodeInput = document.getElementById("codigo_administrador");
    const loginPasswordContainers = document.querySelectorAll('.input-password-container'); // Ajusta el selector si es necesario

    loginPasswordContainers.forEach((container) => {
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

    // Asegurarnos de que los elementos se encontraron (esto ya lo tenías)
    if (emailInput && adminCodeContainer && adminCodeInput) {
        function showAdminCodeField() {
            adminCodeContainer.style.display = "block";
            adminCodeInput.required = true;
        }

        function hideAdminCodeField() {
            adminCodeContainer.style.display = "none";
            adminCodeInput.required = false;
        }

        function checkEmailRole(email) {
            if (email.trim() === "") {
                hideAdminCodeField();
                return;
            }

            const csrfTokenMeta = document.querySelector(
                'meta[name="csrf-token"]'
            );
            if (!csrfTokenMeta) {
                console.error(
                    'Meta tag "csrf-token" no encontrada en el HEAD.'
                );
                hideAdminCodeField();
                return;
            }
            const csrfToken = csrfTokenMeta.getAttribute("content");

            fetch(window.appConfig.checkEmailRoleUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({ email: email }),
            })
                .then((response) => {
                    if (!response.ok) {
                        console.error(
                            "Error en la petición AJAX:",
                            response.status,
                            response.statusText
                        );
                        hideAdminCodeField();
                        return null;
                    }
                    return response.json(); // Intentar parsear como JSON
                })
                .then((data) => {
                    if (data && data.is_admin) {
                        // Verificar que 'data' no es nulo/indefinido y que 'is_admin' es true
                        showAdminCodeField();
                    } else {
                        hideAdminCodeField();
                    }
                })
                .catch((error) => {
                    console.error(
                        "DEBUG JS: Error in fetch or processing AJAX response:",
                        error
                    ); // Log de depuración
                    hideAdminCodeField();
                });
        }

        // Event listeners
        emailInput.addEventListener("blur", function () {
            checkEmailRole(this.value);
        });

        let typingTimer;
        const doneTypingInterval = 500;
        emailInput.addEventListener("input", function () {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
                checkEmailRole(this.value);
            }, doneTypingInterval);
        });

        // Chequeo inicial
        if (emailInput.value.trim() !== "") {
            checkEmailRole(emailInput.value);
        }
    } else {
        console.error(
            "Elementos necesarios (email-input, admin-code-container, o codigo_administrador) no encontrados en el DOM."
        ); // Este error ya lo vimos, si no sale, bien.
    }
});
