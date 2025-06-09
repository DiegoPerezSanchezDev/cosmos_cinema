import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

document.addEventListener('DOMContentLoaded', function () {
    const heroSwiperElement = document.querySelector('.hero-movie-swiper');
    const rawPeliculasDataFromWindow = window.heroSliderPeliculasData;
    let peliculasData;

    if (Array.isArray(rawPeliculasDataFromWindow)) {
        peliculasData = rawPeliculasDataFromWindow;
    } else if (typeof rawPeliculasDataFromWindow === 'object' && rawPeliculasDataFromWindow !== null) {
        peliculasData = Object.values(rawPeliculasDataFromWindow);
        if (peliculasData.length > 0 && typeof peliculasData[0] !== 'object') {
            console.warn("[HeroSwiper] La conversión con Object.values() no resultó en un array de objetos. Revisar la estructura de window.heroSliderPeliculasData.");
        }
    } else {
        peliculasData = [];
    }

    function updateSlideContent(slideElement, peliculaData) {
        if (!slideElement || !peliculaData) {
            return;
        }
        const imgEl = slideElement.querySelector('.hero-slide-background-image');
        const titleEl = slideElement.querySelector('.hero-slide-title');
        if (imgEl) {
            imgEl.alt = `Imagen de fondo de ${peliculaData && peliculaData.titulo ? peliculaData.titulo : 'Película'}`;
        }
        if (titleEl && peliculaData && peliculaData.titulo) {
            titleEl.textContent = peliculaData.titulo;
        } else if (titleEl) {
            titleEl.textContent = 'Sin Título';
        }
    }

    if (heroSwiperElement && peliculasData && peliculasData.length > 0) {
        const swiperSlides = Array.from(heroSwiperElement.querySelectorAll('.swiper-slide'));
        heroSwiperElement.classList.add('swiper-initialized');

        swiperSlides.forEach((slide) => {
            const slideIndexAttr = slide.dataset.slideIndex;
            if (slideIndexAttr !== undefined) {
                const slideIndex = parseInt(slideIndexAttr, 10);
                if (!isNaN(slideIndex) && peliculasData[slideIndex]) {
                    updateSlideContent(slide, peliculasData[slideIndex]);
                }
            } else {
                console.warn("[HeroSwiper] Slide DOM no tiene 'data-slide-index'. Esto es crucial.");
            }
        });

        if (swiperSlides.length > 0) {
            try {
                const heroSwiper = new Swiper(heroSwiperElement, {
                    modules: [Navigation, Pagination, Autoplay],
                    slidesPerView: 1,
                    spaceBetween: 0,
                    loop: peliculasData.length > 1,
                    grabCursor: peliculasData.length > 1,
                    pagination: {
                        el: '.hero-swiper-pagination',
                        clickable: true,
                    },
                    navigation: {
                        nextEl: '.hero-swiper-button-next',
                        prevEl: '.hero-swiper-button-prev',
                    },
                    autoplay: {
                        delay: 5000,
                        disableOnInteraction: false,
                    },
                    watchOverflow: true,
                    observer: true,
                    observeParents: true,
                    on: {
                        init: function (swiper) {
                            if (swiper.navigation) swiper.navigation.update();
                            if (swiper.pagination) swiper.pagination.update();

                            const activeRealIndex = swiper.realIndex;
                            const activeSlideElement = swiper.slides[swiper.activeIndex];
                            if (activeSlideElement && peliculasData[activeRealIndex]) {
                                updateSlideContent(activeSlideElement, peliculasData[activeRealIndex]);
                            }
                        },
                        slideChangeTransitionEnd: function (swiper) {
                            if (swiper.pagination) {
                                swiper.pagination.render();
                                swiper.pagination.update();
                            }
                            if (swiper.navigation) swiper.navigation.update();

                            const activeRealIndex = swiper.realIndex;
                            const activeSlideElement = swiper.slides[swiper.activeIndex];
                            if (activeSlideElement && peliculasData[activeRealIndex]) {
                                updateSlideContent(activeSlideElement, peliculasData[activeRealIndex]);
                            }
                        },
                        resize: function (swiper) {
                            swiper.slides.forEach((slide) => {
                                const slideIndexAttr = slide.dataset.slideIndex;
                                if (slideIndexAttr !== undefined) {
                                    const slideIndex = parseInt(slideIndexAttr, 10);
                                    if (!isNaN(slideIndex) && peliculasData[slideIndex]) {
                                        updateSlideContent(slide, peliculasData[slideIndex]);
                                    }
                                }
                            });
                            if (swiper.navigation) swiper.navigation.update();
                        },
                    }
                });
            } catch (e) {
                console.error("[HeroSwiper] ERROR AL INICIALIZAR SWIPER:", e);
                console.error(e.stack);
            }
        } else {
            console.warn("[HeroSwiper] No se encontraron elementos .swiper-slide para inicializar.");
        }
    } else {
        if (!heroSwiperElement) {
            console.warn("[HeroSwiper] Elemento .hero-movie-swiper no encontrado en el DOM.");
        }
        if (!peliculasData || peliculasData.length === 0) {
            console.warn("[HeroSwiper] No hay datos de películas (peliculasData está vacío o no es un array con elementos).");
        }
        console.warn("[HeroSwiper] Swiper no se inicializará.");
    }
});