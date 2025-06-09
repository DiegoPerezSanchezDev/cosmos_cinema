import Chart from 'chart.js/auto';

document.addEventListener('DOMContentLoaded', () => {
    const sidebarLinks = document.querySelectorAll('.sidebar-link[data-section]');
    const contentSections = document.querySelectorAll('.content-section');

    const searchInput = document.getElementById('api-search-input');
    const listTypeSelect = document.getElementById('api-list-type-select');
    const genreSelect = document.getElementById('api-genre-select');
    const quantitySelect = document.getElementById('api-quantity-select');
    const languageSelect = document.getElementById('api-language-select');

    const searchButton = document.getElementById('api-search-button');
    const resultsArea = document.querySelector('.api-results-area');
    const pageInfoSpan = document.getElementById('page-info');
    const prevPageBtn = document.getElementById('prev-page-btn');
    const nextPageBtn = document.getElementById('next-page-btn');

    const menuToggle = document.querySelector('.menu-toggle');
    const mainContainer = document.querySelector('.main-dashboard-content');

    if (menuToggle && mainContainer) {
        menuToggle.addEventListener('click', function() {
            mainContainer.classList.toggle('sidebar-open');
        });

        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 431) {
                    mainContainer.classList.remove('sidebar-open');
                }
            });
        });
    }

    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 431) {
                mainContainer.classList.remove('sidebar-open');
            }
        });
    });

    const updateSearchInputState = () => {
        if (listTypeSelect.value === 'search') {
            searchInput.disabled = false;
            searchInput.placeholder = 'Buscar por título';
        } else {
            searchInput.disabled = true;
            searchInput.value = '';
            searchInput.placeholder = 'Búsqueda por título deshabilitada';
        }
    };

    /* listTypeSelect.addEventListener('change', updateSearchInputState);
    updateSearchInputState(); */

    let allFetchedMovies = [];
    let currentPage = 1;
    const itemsPerPage = 5;

    const displayMessage = (message) => {
        resultsArea.innerHTML = `<p>${message}</p>`;
        updatePaginationControls(0);
    };

    const displayError = (message = 'Ocurrió un error al buscar películas.') => {
        resultsArea.innerHTML = `<p style="color: red;">${message}</p>`;
        updatePaginationControls(0);
    };

    const updatePaginationControls = (totalMoviesCount) => {
        const totalDisplayPages = Math.ceil(totalMoviesCount / itemsPerPage);
        pageInfoSpan.textContent = `Página ${currentPage} de ${totalDisplayPages || 1}`;
        prevPageBtn.disabled = currentPage === 1;
        nextPageBtn.disabled = currentPage === totalDisplayPages || totalDisplayPages === 0;
    };

    const buildMovieItemHtml = (movie) => {
        const posterBaseUrl = 'https://image.tmdb.org/t/p/w200/';
        const posterUrl = movie.poster_path ? `${posterBaseUrl}${movie.poster_path}` : 'https://via.placeholder.com/80x120?text=No+Poster';
        const buttonText = movie.is_added ? 'Añadida' : 'Añadir pelicula';
        const buttonDisabled = movie.is_added ? 'disabled' : '';
        const buttonClass = movie.is_added ? 'add-movie-btn added' : 'add-movie-btn';

        return `
            <div class="api-movie-item">
                <img src="${posterUrl}" alt="Poster de ${movie.title || 'Película sin título'}">
                <div class="movie-details">
                    <h4>${movie.title || 'Película sin título'} (${movie.release_date ? movie.release_date.split('-')[0] : 'Año desconocido'})</h4>
                    <p class='sinopsis'>${movie.overview ? movie.overview.substring(0, 150) + '...' : 'Sinopsis no disponible.'}</p>
                </div>
                <button class="${buttonClass}" data-tmdb-id="${movie.id}" ${buttonDisabled}>${buttonText}</button>
            </div>
        `;
    };

    const renderCurrentPageMovies = () => {
        resultsArea.innerHTML = '';
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const moviesToDisplay = allFetchedMovies.slice(startIndex, endIndex);

        if (moviesToDisplay.length === 0 && allFetchedMovies.length > 0) {
            currentPage = Math.max(1, currentPage - 1);
            renderCurrentPageMovies();
            return;
        }

        moviesToDisplay.forEach(movie => {
            resultsArea.innerHTML += buildMovieItemHtml(movie);
        });

        const addMovieButtons = resultsArea.querySelectorAll('.add-movie-btn:not(.added)');
        addMovieButtons.forEach(button => {
            button.addEventListener('click', async (event) => {
                const tmdbId = button.dataset.tmdbId;
                button.disabled = true;
                const originalButtonText = button.textContent;
                button.textContent = 'Añadiendo...';
                try {
                    const response = await fetch('/administrador/movies', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ tmdb_id: tmdbId })
                    });
                    const result = await response.json();
                    if (response.ok) {
                        if (response.status === 409) {
                            button.textContent = 'Ya añadida';
                            button.classList.add('added');
                        } else if (result.status === 'success') {
                            button.textContent = 'Añadida';
                            button.classList.add('added');
                        } else if (result.status === 'duplicate') {
                            button.textContent = 'Ya añadida';
                            button.classList.add('added');
                        } else {
                            alert(result.message || 'Operación completada con estado desconocido.');
                            button.textContent = originalButtonText;
                            button.disabled = false;
                            button.classList.remove('added');
                        }
                    } else {
                        alert('Error: ' + (result.error || `Error desconocido en el servidor (Estado ${response.status}).`));
                        button.textContent = originalButtonText;
                        button.disabled = false;
                        button.classList.remove('added');
                    }
                } catch (error) {
                    alert('Error al intentar añadir la película: ' + error.message);
                    button.textContent = originalButtonText;
                    button.disabled = false;
                    button.classList.remove('added');
                }
            });
        });
        updatePaginationControls(allFetchedMovies.length);
    };

    if (prevPageBtn) {
        prevPageBtn.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                renderCurrentPageMovies();
            }
        });
    }

    if (nextPageBtn) {
        nextPageBtn.addEventListener('click', () => {
            const totalDisplayPages = Math.ceil(allFetchedMovies.length / itemsPerPage);
            if (currentPage < totalDisplayPages) {
                currentPage++;
                renderCurrentPageMovies();
            }
        });
    }

    const performSearch = async () => {
        const query = searchInput.value.trim();
        const listType = listTypeSelect.value;
        const genreId = genreSelect.value;
        const pagesToFetch = quantitySelect.value;
        const searchLanguage = languageSelect.value;

        if (listType === 'search' && query === '') {
            displayMessage('Por favor, introduce un título para buscar.');
            allFetchedMovies = [];
            currentPage = 1;
            updatePaginationControls(0);
            return;
        }

        displayMessage('Buscando películas...');
        searchButton.disabled = true;
        prevPageBtn.disabled = true;
        nextPageBtn.disabled = true;

        try {
            const queryParams = new URLSearchParams({
                query: query,
                list_type: listType,
                genre_id: genreId,
                pages_to_fetch: pagesToFetch,
                language: searchLanguage
            }).toString();
            const response = await fetch(`/administrador/buscar-peliculas-api?${queryParams}`, {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' },
            });
            if (!response.ok) {
                const errorData = await response.json();
                const errorMessage = errorData.error || `Error HTTP: ${response.status}`;
                throw new Error(errorMessage);
            }
            const movies = await response.json();
            allFetchedMovies = movies;
            currentPage = 1;
            renderCurrentPageMovies();
        } catch (error) {
            displayError(error.message);
            allFetchedMovies = [];
        } finally {
            searchButton.disabled = false;
        }
    };

    if (searchButton) {
        searchButton.addEventListener('click', performSearch);
    }
    

    const showSection = (sectionId) => {
        contentSections.forEach(section => {
            section.classList.add('hidden');
        });
        const targetSection = document.getElementById(sectionId);
        if (targetSection) {
            targetSection.classList.remove('hidden');
            targetSection.dispatchEvent(new CustomEvent('sectionShown', { detail: { sectionId: sectionId } }));
        }
        sidebarLinks.forEach(link => {
            link.classList.remove('active');
        });
        const activeLink = document.querySelector(`.sidebar-link[data-section="${sectionId.replace('-section', '')}"]`);
        if (activeLink) {
            activeLink.classList.add('active');
        }
        localStorage.setItem('activeAdminSection', sectionId);
    };

    sidebarLinks.forEach(link => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            const sectionId = link.dataset.section + '-section';
            showSection(sectionId);
        });
    });

    const savedSectionId = localStorage.getItem('activeAdminSection');
    if (savedSectionId && document.getElementById(savedSectionId)) {
        showSection(savedSectionId);
    } else if (sidebarLinks.length > 0 && sidebarLinks[0].dataset.section) {
        showSection(sidebarLinks[0].dataset.section + '-section');
    }


    const facturacionSection = document.getElementById('facturacion-section');
    if (facturacionSection) {
        const factChartCanvas = document.getElementById('fact-ingresosMensualesChart');
        const factChartYearForm = document.getElementById('fact-chartYearForm');
        const factSelectChartYear = document.getElementById('fact-select_chart_year');
        const factChartYearDisplay = document.getElementById('fact-chartYear');

        const factResumenBrutoHoyEl = document.getElementById('fact-resumen-bruto-hoy');
        const factResumenImpuestosHoyEl = document.getElementById('fact-resumen-impuestos-hoy');
        const factResumenNetoHoyEl = document.getElementById('fact-resumen-neto-hoy');
        const factResumenNumFacturasHoyEl = document.getElementById('fact-resumen-num-facturas-hoy');

        const factFacturasTbody = document.getElementById('fact-facturas-list');
        const factPaginationLinksContainer = document.getElementById('fact-pagination-links');
        const factFilterForm = document.getElementById('fact-filter-form');
        const factApplyFiltersBtn = document.getElementById('fact-apply-filters-btn');
        const factClearFiltersBtn = document.getElementById('fact-clear-filters-btn');

        const factReportTypeSelect = document.getElementById('fact-report-type');
        const factDiarioControls = document.getElementById('fact-diario-controls');
        const factMensualControlsMes = document.getElementById('fact-mensual-controls-mes');
        const factMensualControlsAno = document.getElementById('fact-mensual-controls-ano');
        const factAnualControls = document.getElementById('fact-anual-controls');
        const factGeneratePdfBtn = document.getElementById('fact-generate-pdf-btn');
        const factReportFechaDiarioInput = document.getElementById('fact-report-fecha_diario');
        const factReportMesMensualSelect = document.getElementById('fact-report-mes_mensual');
        const factReportAnoMensualSelect = document.getElementById('fact-report-ano_mensual');
        const factReportAnoAnualSelect = document.getElementById('fact-report-ano_anual');

        let factIngresosChartInstance;

        const factFormatCurrency = (value) => {
            if (value === null || value === undefined || isNaN(parseFloat(value))) return '0,00 €';
            return parseFloat(value).toLocaleString('es-ES', { style: 'currency', currency: 'EUR' });
        };

        const factLoadResumenDiario = async () => {
            try {
                const response = await fetch('/administrador/facturacion/resumen-hoy');
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const data = await response.json();
                if (factResumenBrutoHoyEl) factResumenBrutoHoyEl.textContent = factFormatCurrency(data.totalBrutoHoy);
                if (factResumenImpuestosHoyEl) factResumenImpuestosHoyEl.textContent = factFormatCurrency(data.totalImpuestosHoy);
                if (factResumenNetoHoyEl) factResumenNetoHoyEl.textContent = factFormatCurrency(data.totalNetoHoy);
                if (factResumenNumFacturasHoyEl) factResumenNumFacturasHoyEl.textContent = data.numFacturasHoy;
            } catch (error) {
                console.error('Error al cargar resumen diario de facturación:', error);
                if (factResumenBrutoHoyEl) factResumenBrutoHoyEl.textContent = 'Error';
                if (factResumenImpuestosHoyEl) factResumenImpuestosHoyEl.textContent = 'Error';
                if (factResumenNetoHoyEl) factResumenNetoHoyEl.textContent = 'Error';
                if (factResumenNumFacturasHoyEl) factResumenNumFacturasHoyEl.textContent = 'Error';
            }
        };

        const factLoadChartData = async (year) => {
            if (!factChartCanvas) return;
            if (factChartYearDisplay) factChartYearDisplay.textContent = year;
            try {
                const response = await fetch(`/administrador/facturacion/charts/ingresos-mensuales?ano=${year}`);
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const apiData = await response.json();
                if (factIngresosChartInstance) {
                    factIngresosChartInstance.destroy();
                }
                factIngresosChartInstance = new Chart(factChartCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: apiData.labels,
                        datasets: apiData.datasets.map(dataset => ({ ...dataset }))
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: { y: { beginAtZero: true, ticks: { callback: value => factFormatCurrency(value) } } },
                        plugins: { tooltip: { callbacks: { label: context => `${context.dataset.label || ''}: ${factFormatCurrency(context.parsed.y)}` } } }
                    }
                });
            } catch (error) {
                console.error('Error al cargar datos del gráfico de facturación:', error);
            }
        };

        const factLoadFacturas = async (url = '/administrador/facturacion/lista-facturas') => {
            if (!factFacturasTbody) return;
            factFacturasTbody.innerHTML = '<tr><td colspan="7" class="text-center">Cargando facturas...</td></tr>';
            try {
                const formData = factFilterForm ? new FormData(factFilterForm) : new FormData();
                const params = new URLSearchParams(formData).toString();
                const fetchUrl = url + (url.includes('?') ? '&' : '?') + params;

                const response = await fetch(fetchUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const data = await response.json();

                factFacturasTbody.innerHTML = '';
                if (data.data && data.data.length > 0) {
                    data.data.forEach(factura => {
                        const impuestoInfo = factura.impuesto ? `${factura.impuesto.tipo} (${parseFloat(factura.impuesto.cantidad).toFixed(2)}%)` : 'N/A';
                        const row = `<tr>
                            <td>${factura.id_factura}</td>
                            <td>${new Date(factura.created_at).toLocaleString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</td>
                            <td>${impuestoInfo}</td>
                            <td class="text-end">${factFormatCurrency(factura.monto_neto_sin_impuesto)}</td>
                            <td class="text-end">${factFormatCurrency(factura.monto_impuesto)}</td>
                            <td class="text-end">${factFormatCurrency(factura.monto_bruto_con_impuesto)}</td>
                        </tr>`;
                        factFacturasTbody.insertAdjacentHTML('beforeend', row);
                    });
                } else {
                    factFacturasTbody.innerHTML = '<tr><td colspan="7" class="text-center">No se encontraron facturas.</td></tr>';
                }
                if (factPaginationLinksContainer && data.links) {
                    factRenderPagination(data.links);
                }
            } catch (error) {
                console.error('Error al cargar facturas:', error);
                factFacturasTbody.innerHTML = '<tr><td colspan="7" class="text-center">Error al cargar facturas.</td></tr>';
            }
        };

        const factRenderPagination = (links) => {
            if (!factPaginationLinksContainer) return;
            factPaginationLinksContainer.innerHTML = '';
            if (!links || links.length === 0) return;

            links.forEach(link => {
                const li = document.createElement('li');
                li.classList.add('page-item');
                if (link.active) li.classList.add('active');
                if (!link.url) li.classList.add('disabled');

                const a = document.createElement('a');
                a.classList.add('page-link');
                a.href = link.url || '#';
                a.innerHTML = link.label;
                if (link.url) {
                    a.addEventListener('click', (e) => {
                        e.preventDefault();
                        factLoadFacturas(link.url);
                    });
                }
                li.appendChild(a);
                factPaginationLinksContainer.appendChild(li);
            });
        };
        
        const factUpdateReportControlsVisibility = () => {
            if (!factReportTypeSelect) return;
            const selectedType = factReportTypeSelect.value;
            [factDiarioControls, factMensualControlsMes, factMensualControlsAno, factAnualControls].forEach(el => {
                if (el) el.style.display = 'none';
            });

            if (selectedType === 'diario') {
                if (factDiarioControls) factDiarioControls.style.display = 'block';
            } else if (selectedType === 'mensual') {
                if (factMensualControlsMes) factMensualControlsMes.style.display = 'block';
                if (factMensualControlsAno) factMensualControlsAno.style.display = 'block';
            } else if (selectedType === 'anual') {
                if (factAnualControls) factAnualControls.style.display = 'block';
            }
        };

        if (factReportTypeSelect) {
            factReportTypeSelect.addEventListener('change', factUpdateReportControlsVisibility);
            factUpdateReportControlsVisibility();
        }

        if (factGeneratePdfBtn) {
            factGeneratePdfBtn.addEventListener('click', () => {
                if (!factReportTypeSelect) return;
                const reportType = factReportTypeSelect.value;
                let targetUrl = '';
                const params = new URLSearchParams();

                if (reportType === 'diario') {
                    targetUrl = '/administrador/reporte/diario';
                    const fechaDiario = factReportFechaDiarioInput ? factReportFechaDiarioInput.value : null;
                    if (!fechaDiario) {
                        alert('Por favor, selecciona una fecha para el reporte diario.');
                        return;
                    }
                    params.append('fecha', fechaDiario);
                } else if (reportType === 'mensual') {
                    targetUrl = '/administrador/reporte/mensual';
                    const mesMensual = factReportMesMensualSelect ? factReportMesMensualSelect.value : null;
                    const anoMensual = factReportAnoMensualSelect ? factReportAnoMensualSelect.value : null;
                    if (!mesMensual || !anoMensual) {
                        alert('Por favor, selecciona mes y año para el reporte mensual.');
                        return;
                    }
                    params.append('mes', mesMensual);
                    params.append('ano', anoMensual);
                } else if (reportType === 'anual') {
                    targetUrl = '/administrador/reporte/anual';
                    const anoAnual = factReportAnoAnualSelect ? factReportAnoAnualSelect.value : null;
                    if (!anoAnual) {
                        alert('Por favor, selecciona un año para el reporte anual.');
                        return;
                    }
                    params.append('ano', anoAnual);
                }

                if (targetUrl) {
                    window.open(`${targetUrl}?${params.toString()}`, '_blank');
                } else {
                    alert('Tipo de reporte no válido seleccionado.');
                }
            });
        }


        if (factChartYearForm && factSelectChartYear) {
            factChartYearForm.addEventListener('submit', (e) => {
                e.preventDefault();
                factLoadChartData(factSelectChartYear.value);
            });
        }

        if (factApplyFiltersBtn) {
            factApplyFiltersBtn.addEventListener('click', () => factLoadFacturas());
        }
        if (factClearFiltersBtn) {
            factClearFiltersBtn.addEventListener('click', () => {
                if (factFilterForm) factFilterForm.reset();
                factLoadFacturas();
            });
        }

        facturacionSection.addEventListener('sectionShown', (event) => {
            if (event.detail.sectionId === 'facturacion-section') {
                factLoadResumenDiario();
                if (factSelectChartYear) factLoadChartData(factSelectChartYear.value);
                factLoadFacturas();
            }
        });

        if (!facturacionSection.classList.contains('hidden')) {
            factLoadResumenDiario();
            if (factSelectChartYear) factLoadChartData(factSelectChartYear.value);
            factLoadFacturas();
        }
    }
});