document.addEventListener("DOMContentLoaded", () => {
    const manageSearchInput = document.getElementById("manage-search-input");
    const manageGenreSelect = document.getElementById("manage-genre-select");
    const manageStatusSelect = document.getElementById("manage-status-select");
    const manageItemsPerPageSelect = document.getElementById(
        "manage-items-per-page-select"
    );
    const manageFilterButton = document.getElementById("manage-filter-button");
    const manageMoviesArea = document.querySelector(".manage-movies-area");
    const managePageInfoSpan = document.getElementById("manage-page-info");
    const managePrevPageBtn = document.getElementById("manage-prev-page-btn");
    const manageNextPageBtn = document.getElementById("manage-next-page-btn");

    let manageCurrentPage = 1;
    let manageMoviesLoaded = false;

    const displayManageMessage = (message) => {
        manageMoviesArea.innerHTML = `<p>${message}</p>`;
        updateManagePaginationControls({});
    };

    const displayManageError = (
        message = "Ocurrió un error al gestionar películas."
    ) => {
        manageMoviesArea.innerHTML = `<p style="color: red;">${message}</p>`;
        updateManagePaginationControls({});
    };

    const buildManagedMovieItemHtml = (movie) => {
        const posterBaseUrl = "https://image.tmdb.org/t/p/w200/";
        const posterUrl = movie.poster_ruta
            ? `${posterBaseUrl}${movie.poster_ruta}`
            : "https://via.placeholder.com/80x120?text=No+Poster";

        const statusButtonText = movie.activa ? "Desactivar" : "Activar";
        const statusButtonClass = movie.activa
            ? "toggle-status-btn deactivate"
            : "toggle-status-btn activate";

        const estrenoButtonText = movie.estreno
            ? "Pasar a Cartelera"
            : "Pasar a Estreno";
        const estrenoButtonClass = movie.estreno
            ? "toggle-estreno-btn deactivate"
            : "toggle-estreno-btn activate";
        const estrenoStatusText = movie.estreno ? "Estreno" : "Cartelera";
        const estrenoStatusClass = movie.estreno
            ? "status-estreno"
            : "status-cartelera";

        return `
            <div class="managed-movie-item">
                <img src="${posterUrl}" alt="Poster de ${
                    movie.titulo || "Película sin título"
                }">
                <div class="movie-details">
                    <h4>${movie.titulo || "Película sin título"} (${
                        movie.fecha_estreno
                            ? movie.fecha_estreno.split("-")[0]
                            : "Año desconocido"
                    })</h4>
                    <p class='sinopsis'>${
                        movie.sinopsis
                            ? movie.sinopsis.substring(0, 150) + "..."
                            : "Sinopsis no disponible."
                    }</p>
                    <p>Estado Activo: <span class="${
                        movie.activa ? "status-active" : "status-inactive"
                    }">${movie.activa ? "Activa" : "Inactiva"}</span></p>
                    <p>Estado: <span class="${estrenoStatusClass}">${estrenoStatusText}</span></p>
                </div>
                <div class="movie-actions">
                    <button class="${statusButtonClass}" data-movie-id="${
                        movie.id
                    }">${statusButtonText}</button>
                    <button class="${estrenoButtonClass}" data-movie-id="${
                        movie.id
                    }">${estrenoButtonText}</button>
                </div>
            </div>
            <div class="status-error-message" data-movie-id="${
                        movie.id
                    }" style="color: red; font-size: 0.9em; margin-top: 5px; margin-bottom: 10px;display: none; text-align: center"></div>
        `;
    };

    const renderManagedMovies = (paginationData) => {
        manageMoviesArea.innerHTML = "";

        const moviesToDisplay = paginationData.data ?? [];

        if (moviesToDisplay.length === 0) {
            displayManageMessage(
                "No existen películas en la base de datos con esos filtros."
            );
            updateManagePaginationControls(paginationData);
            return;
        }

        moviesToDisplay.forEach((movie) => {
            manageMoviesArea.innerHTML += buildManagedMovieItemHtml(movie);
        });

        const toggleStatusButtons =
            manageMoviesArea.querySelectorAll(".toggle-status-btn");

        toggleStatusButtons.forEach((button) => {
            button.addEventListener("click", async (event) => {
                const movieId = button.dataset.movieId;
                const originalButtonText = button.textContent;

                button.disabled = true;
                button.textContent = "Cambiando...";

                try {
                    const response = await fetch(
                        `/administrador/movies/${movieId}/estadoActivo`,
                        {
                            method: "PATCH",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document
                                    .querySelector('meta[name="csrf-token"]')
                                    .getAttribute("content"),
                            },
                        }
                    );

                    const result = await response.json();

                    if (response.ok) {
                        console.log(result.message);
                        button.textContent = result.new_status
                            ? "Desactivar"
                            : "Activar";
                        button.classList.remove("activate", "deactivate");
                        button.classList.add(
                            result.new_status ? "deactivate" : "activate"
                        );
                        const statusSpan = button
                            .closest(".managed-movie-item")
                            .querySelector(".status-active, .status-inactive");
                        if (statusSpan) {
                            statusSpan.textContent = result.new_status
                                ? "Activa"
                                : "Inactiva";
                            statusSpan.classList.remove(
                                "status-active",
                                "status-inactive"
                            );
                            statusSpan.classList.add(
                                result.new_status
                                    ? "status-active"
                                    : "status-inactive"
                            );
                        }
                    } else {
                        const errorMessageDiv = manageMoviesArea.querySelector(
                            `.status-error-message[data-movie-id="${movieId}"]`
                        );
                        const errorMessage =
                            result.message || "Error al cambiar el estado.";
                        if (errorMessageDiv) {
                            errorMessageDiv.textContent = errorMessage;
                            errorMessageDiv.style.display = "block";
                            setTimeout(() => {
                                errorMessageDiv.style.display = "none";
                                errorMessageDiv.textContent = "";
                            }, 3000);
                        }
                        console.error("Error response from backend:", result);
                        button.textContent = originalButtonText;
                    }
                } catch (error) {
                    console.error(
                        "Error al cambiar estado de película:",
                        error
                    );
                    const errorMessageDiv = manageMoviesArea.querySelector(
                        `.status-error-message[data-movie-id="${movieId}"]`
                    );
                    if (errorMessageDiv) {
                        errorMessageDiv.textContent =
                            "Error al intentar cambiar el estado.";
                        errorMessageDiv.style.display = "block";
                        setTimeout(() => {
                            errorMessageDiv.style.display = "none";
                            errorMessageDiv.textContent = "";
                        }, 3000);
                    }
                    button.textContent = originalButtonText;
                } finally {
                    button.disabled = false;
                }
            });
        });

        const toggleEstrenoButtons = manageMoviesArea.querySelectorAll(
            ".toggle-estreno-btn"
        );
        toggleEstrenoButtons.forEach((button) => {
            button.addEventListener("click", async (event) => {
                const movieId = button.dataset.movieId;
                const originalButtonText = button.textContent;

                button.disabled = true;
                button.textContent = "Cambiando...";

                try {
                    const response = await fetch(
                        `/administrador/movies/${movieId}/estrenoActivo`,
                        {
                            method: "PATCH",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document
                                    .querySelector('meta[name="csrf-token"]')
                                    .getAttribute("content"),
                            },
                        }
                    );

                    const result = await response.json();

                    if (response.ok) {
                        console.log(result.message);
                        button.textContent = result.new_status
                            ? "Pasar a Cartelera"
                            : "Pasar a Estreno";
                        button.classList.remove("activate", "deactivate");
                        button.classList.add(
                            result.new_status ? "deactivate" : "activate"
                        );

                        const estrenoStatusSpan = button
                            .closest(".managed-movie-item")
                            .querySelector(".status-cartelera, .status-estreno");
                        if (estrenoStatusSpan) {
                            estrenoStatusSpan.textContent =
                                result.new_status_text;
                            estrenoStatusSpan.classList.remove(
                                "status-cartelera",
                                "status-estreno"
                            );
                            estrenoStatusSpan.classList.add(
                                result.new_status
                                    ? "status-estreno"
                                    : "status-cartelera"
                            );
                        }
                    } else {
                        alert(
                            "Error: " +
                                (result.error ||
                                    `Error al cambiar estado de estreno (Estado ${response.status}).`)
                        );
                        console.error("Error response from backend:", result);
                        button.textContent = originalButtonText;
                    }
                } catch (error) {
                    console.error(
                        "Error al cambiar estado de estreno de película:",
                        error
                    );
                    alert(
                        "Error al intentar cambiar el estado de estreno: " +
                            error.message
                    );
                    button.textContent = originalButtonText;
                } finally {
                    button.disabled = false;
                }
            });
        });

        updateManagePaginationControls(paginationData);
    };

    const updateManagePaginationControls = (paginationData) => {
        const currentPage = paginationData.current_page ?? 0;
        const lastPage = paginationData.last_page ?? 0;
        const total = paginationData.total ?? 0;
        const perPage = paginationData.per_page ?? 0;

        managePageInfoSpan.textContent = `Página ${currentPage} de ${
            lastPage || 1
        } (${total} películas en total)`;

        managePrevPageBtn.disabled = currentPage <= 1;
        manageNextPageBtn.disabled = currentPage >= lastPage || lastPage === 0;

        manageCurrentPage = currentPage;
    };

    const fetchManagedMovies = async (page = 1) => {
        if (manageFilterButton.disabled) {
            console.log("Carga de películas gestionadas ya en progreso.");
            return;
        }

        const query = manageSearchInput.value.trim();
        const genreId = manageGenreSelect.value;
        const status = manageStatusSelect.value;
        const itemsPerPage = manageItemsPerPageSelect.value;

        displayManageMessage("Cargando películas...");
        manageFilterButton.disabled = true;
        managePrevPageBtn.disabled = true;
        manageNextPageBtn.disabled = true;

        try {
            const queryParams = new URLSearchParams({
                query: query,
                genre_id: genreId,
                status: status,
                items_per_page: itemsPerPage,
                page: page,
            }).toString();

            const response = await fetch(
                `/administrador/manage-movies?${queryParams}`,
                {
                    method: "GET",
                    headers: { "Content-Type": "application/json" },
                }
            );

            if (!response.ok) {
                let errorMessage = `Error HTTP: ${response.status}`;
                try {
                    const errorData = await response.json();
                    errorMessage = errorData.error || errorMessage;
                } catch (jsonError) {
                    console.error(
                        "Error parsing error response JSON:",
                        jsonError
                    );
                }
                displayManageError(errorMessage);
                updateManagePaginationControls({});
                return;
            }

            const paginationData = await response.json();

            renderManagedMovies(paginationData);

            manageMoviesLoaded = true;
        } catch (error) {
            console.error("Error fetching managed movies:", error);
            displayManageError(error.message);
            updateManagePaginationControls({});
        } finally {
            manageFilterButton.disabled = false;
        }
    };

    manageFilterButton.addEventListener("click", () => {
        manageCurrentPage = 1;
        fetchManagedMovies(manageCurrentPage);
    });

    managePrevPageBtn.addEventListener("click", () => {
        if (manageCurrentPage > 1) {
            fetchManagedMovies(manageCurrentPage - 1);
        }
    });

    manageNextPageBtn.addEventListener("click", () => {
        if (!manageNextPageBtn.disabled) {
            fetchManagedMovies(manageCurrentPage + 1);
        }
    });

    const manageSection = document.getElementById("manage-movies-section");
    if (manageSection) {
        manageSection.addEventListener("sectionShown", (event) => {
            if (
                event.detail.sectionId === "manage-movies-section" &&
                !manageMoviesLoaded
            ) {
                fetchManagedMovies();
            } else if (
                event.detail.sectionId === "manage-movies-section" &&
                manageMoviesLoaded
            ) {
            }
        });
        document.addEventListener("movieAdded", (event) => {
            if (manageSection && !manageSection.classList.contains("hidden")) {
                fetchManagedMovies(manageCurrentPage);
            }
        });
    }
});