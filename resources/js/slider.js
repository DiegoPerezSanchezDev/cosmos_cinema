import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
// Importar ScrollToPlugin
import { ScrollToPlugin } from "gsap/ScrollToPlugin";

// Asegúrate de que ScrollTrigger y ScrollToPlugin estén disponibles y registrados.
gsap.registerPlugin(ScrollTrigger, ScrollToPlugin);

document.addEventListener("DOMContentLoaded", function () {
    const sliderContainer = document.querySelector('[data-slider="list"]');
    const slides = gsap.utils.toArray('[data-slider="slide"]');
    const prevBtn = document.querySelector('[data-slider="button-prev"]');
    const nextBtn = document.querySelector('[data-slider="button-next"]');
    // Seleccionar el botón de cerrar compra (si existe)
    const cerrarCompraBtn = document.getElementById("cerrarCompraBtn");

    if (!sliderContainer || slides.length === 0) {
        console.warn(
            "Slider elements not found. Skipping slider initialization."
        );
        return;
    }

    const totalElement = document.querySelector('[data-slide-count="total"]');
    const stepElement = document.querySelector('[data-slide-count="step"]');
    const stepsParent = stepElement ? stepElement.parentElement : null;
    let allSteps = [];

    const slideCount = slides.length;

    if (totalElement) {
        totalElement.textContent =
            slideCount < 10 ? `0${slideCount}` : slideCount;
    }

    if (stepsParent && stepElement) {
        stepsParent.innerHTML = "";
        let aux = true;
        slides.forEach((_, index) => {
            if (aux) {
                const stepClone = document.createElement("div"); //create a div
                stepClone.classList.add("count-column"); // add the class
                stepClone.innerHTML = `<h2 data-slide-count="step" class="count-heading"></h2>`; // add the h2
                const stepContent = stepClone.querySelector(
                    '[data-slide-count="step"]'
                ); //select the h2
                stepContent.textContent =
                    index + 1 < 10 ? `0${index + 1}` : index + 1;
                stepsParent.appendChild(stepClone);
                aux = false;
            }
        });
        allSteps = stepsParent.querySelectorAll('[data-slide-count="step"]');
    }

    // Implementación de horizontalLoop para crear un bucle infinito
    function horizontalLoop(items, config) {
        items = gsap.utils.toArray(items);
        if (items.length === 0) {
            console.warn("horizontalLoop: No items found.");
            return null;
        }
        config = config || {};

        let tl = gsap.timeline({
                repeat: config.repeat === undefined ? 0 : config.repeat,
                paused: config.paused,
                defaults: { ease: "none" },
            }),
            length = items.length,
            startX = items[0].offsetLeft,
            times = [],
            widths = [],
            spaceBefore = [],
            xPercents = [],
            curIndex = 0,
            indexIsDirty = false,
            center = config.center,
            pixelsPerSecond =
                (config.speed === undefined ? 1 : config.speed) * 100,
            snap =
                config.snap === false
                    ? (v) => v
                    : gsap.utils.snap(config.snap || 1),
            timeOffset = 0,
            container =
                center === true
                    ? items[0].parentNode
                    : gsap.utils.toArray(center)[0] || items[0].parentNode,
            totalWidth,
            getTotalWidth = () => {
                let total = 0;
                items.forEach((item) => {
                    total +=
                        item.offsetWidth * gsap.getProperty(item, "scaleX") +
                        parseFloat(gsap.getProperty(item, "marginLeft", "px")) +
                        parseFloat(gsap.getProperty(item, "marginRight", "px"));
                });
                return total;
            },
            populateWidths = () => {
                let b1 = container.getBoundingClientRect(),
                    b2;
                items.forEach((el, i) => {
                    widths[i] = parseFloat(gsap.getProperty(el, "width", "px"));
                    xPercents[i] = snap(
                        (parseFloat(gsap.getProperty(el, "x", "px")) /
                            widths[i]) *
                            100 +
                            gsap.getProperty(el, "xPercent")
                    );
                    b2 = el.getBoundingClientRect();
                    spaceBefore[i] = b2.left - (i ? b1.right : b1.left);
                    b1 = b2;
                });
                gsap.set(items, { xPercent: (i) => xPercents[i] });
                totalWidth = getTotalWidth();
            },
            timeWrap,
            populateOffsets = () => {
                timeOffset = center
                    ? (tl.duration() * (container.offsetWidth / 2)) / totalWidth
                    : 0;
                center &&
                    times.forEach((t, i) => {
                        times[i] = timeWrap(
                            tl.labels["label" + i] +
                                (tl.duration() * widths[i]) / 2 / totalWidth -
                                timeOffset
                        );
                    });
            },
            getClosest = (values, value, wrap) => {
                let i = values.length,
                    closest = 1e10,
                    index = 0,
                    d;
                while (i--) {
                    d = Math.abs(values[i] - value);
                    if (d > wrap / 2) {
                        d = wrap - d;
                    }
                    if (d < closest) {
                        closest = d;
                        index = i;
                    }
                }
                return index;
            },
            populateTimeline = () => {
                let i, item, curX, distanceToStart, distanceToLoop;
                tl.clear();
                totalWidth = getTotalWidth();
                for (i = 0; i < length; i++) {
                    item = items[i];
                    curX = (xPercents[i] / 100) * widths[i];
                    distanceToStart =
                        item.offsetLeft + curX - startX + spaceBefore[0];
                    distanceToLoop =
                        distanceToStart +
                        widths[i] * gsap.getProperty(item, "scaleX");

                    tl.to(
                        item,
                        {
                            xPercent: snap(
                                ((curX - distanceToLoop) / widths[i]) * 100
                            ),
                            duration: distanceToLoop / pixelsPerSecond,
                        },
                        0 // Start this tween at the beginning of the timeline segment for this item
                    )
                        .fromTo(
                            item,
                            {
                                // Calculate the starting xPercent for when this item wraps around
                                xPercent: snap(
                                    ((curX - distanceToLoop + totalWidth) /
                                        widths[i]) *
                                        100
                                ),
                            },
                            {
                                xPercent: xPercents[i], // The target xPercent for the fromTo (original position)
                                duration:
                                    (curX -
                                        distanceToLoop +
                                        totalWidth -
                                        curX) /
                                    pixelsPerSecond,
                                immediateRender: false,
                            },
                            distanceToLoop / pixelsPerSecond // This is the position on the timeline where this fromTo tween starts
                        )
                        .add("label" + i, distanceToStart / pixelsPerSecond);
                    times[i] = distanceToStart / pixelsPerSecond;
                }
                timeWrap = gsap.utils.wrap(0, tl.duration());
            },
            refresh = (deep) => {
                let progress = tl.progress();
                tl.progress(0, true);
                populateWidths();
                deep && populateTimeline();
                populateOffsets();
                tl.time(timeWrap(times[curIndex]), true);
            },
            onResize = () => refresh(true);

        gsap.set(items, { x: 0 });
        populateWidths();
        populateTimeline();
        populateOffsets();

        window.addEventListener("resize", onResize);

        // Función para navegar a un índice específico
        function toIndex(index, vars) {
            vars = vars || {};
            Math.abs(index - curIndex) > length / 2 &&
                (index += index > curIndex ? -length : length); // always go in the shortest direction
            let newIndex = gsap.utils.wrap(0, length, index),
                time = times[newIndex];
            if (time > tl.time() !== index > curIndex) {
                // if we're wrapping the timeline's playhead, make the proper adjustments
                vars.modifiers = { time: gsap.utils.wrap(0, tl.duration()) };
                time += tl.duration() * (index > curIndex ? 1 : -1);
            }
            curIndex = newIndex;
            vars.overwrite = true;
            return tl.tweenTo(time, vars);
        }

        // Métodos de la timeline para controlar el slider
        tl.toIndex = (index, vars) => toIndex(index, vars);
        tl.closestIndex = (setCurrent) => {
            let index = getClosest(times, tl.time(), tl.duration());
            if (setCurrent) {
                curIndex = index;
                indexIsDirty = false;
            }
            return index;
        };
        tl.current = () => (indexIsDirty ? tl.closestIndex(true) : curIndex);
        tl.next = (vars) => {
            toIndex(tl.current() + 1, vars);
        };
        tl.previous = (vars) => {
            toIndex(tl.current() - 1, vars);
        };
        tl.times = times;

        // Manejo de la clase 'active' y conteo de pasos
        let lastActiveIndex = -1;
        tl.eventCallback("onUpdate", () => {
            const activeSlideIndex = gsap.utils.wrap(
                0,
                slideCount,
                tl.closestIndex() + 1
            );

            if (activeSlideIndex !== lastActiveIndex) {
                if (lastActiveIndex !== -1 && slides[lastActiveIndex]) {
                    slides[lastActiveIndex].classList.remove("active");
                }

                if (slides[activeSlideIndex]) {
                    slides[activeSlideIndex].classList.add("active");
                }

                if (allSteps.length > 0) {
                    // Update the content of the steps
                    allSteps.forEach((step, index) => {
                        const movie = peliculas[activeSlideIndex]; // Access the movie data
                        step.innerHTML = `<h2 data-slide-count="step" class="count-heading">${movie.titulo}</h2>`;
                    });
                }

                lastActiveIndex = activeSlideIndex;
            }
        });

        tl.progress(1, true).progress(0, true);

        if (config.reversed) {
            tl.vars.onReverseComplete();
            tl.reverse();
        }

        // Posiciona el slider inicialmente para que el Layout 2 (índice 1) sea el activo.
        // Esto se logra alineando el slide 0 (índice 0) a la izquierda.
        tl.toIndex(0, { duration: 0 }); // Alinea el primer slide (índice 0) a la izquierda instantáneamente.

        if (config.repeat === -1 && !config.paused) {
            tl.play();
        }

        return tl;
    }

    // Objeto de configuración del slider
    const mainSliderConfig = {
        repeat: -1,
        speed: 1.2, // Velocidad ajustada
        paused: false,
    };

    const mainSlider = horizontalLoop(slides, mainSliderConfig);

    // Lógica de Reproducción Automática y Pausa
    let autoPlayTimeout;
    const autoPlayDelay = 5000;

    const startAutoPlayTimeout = () => {
        clearTimeout(autoPlayTimeout);
        autoPlayTimeout = setTimeout(() => {
            if (
                mainSlider &&
                mainSlider.paused() &&
                mainSliderConfig.repeat === -1
            ) {
                mainSlider.play();
            }
        }, autoPlayDelay);
    };

    const pauseAndRestartTimeout = () => {
        if (
            mainSlider &&
            !mainSlider.paused() &&
            mainSliderConfig.repeat === -1
        ) {
            mainSlider.pause();
        }
        startAutoPlayTimeout();
    };

    if (mainSlider) {
        if (nextBtn) {
            nextBtn.addEventListener("click", () => {
                pauseAndRestartTimeout();
                mainSlider.next({ ease: "power3.out", duration: 0.725 });
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener("click", () => {
                pauseAndRestartTimeout();
                mainSlider.previous({ ease: "power3.out", duration: 0.725 });
            });
        }

        // Listeners para Clic en los Lados
        const sliderMainArea = document.querySelector(".list");
        if (sliderMainArea) {
            sliderMainArea.addEventListener("click", (event) => {
                if (
                    event.target.closest('[data-slider="button-prev"]') ||
                    event.target.closest('[data-slider="button-next"]') ||
                    event.target.closest('[data-slider="slide"]')
                ) {
                    return;
                }

                pauseAndRestartTimeout();

                const clickX = event.clientX;
                const elementRect = sliderMainArea.getBoundingClientRect();
                const elementMidpointX =
                    elementRect.left + elementRect.width / 2;

                if (clickX > elementMidpointX) {
                    mainSlider.next({ ease: "power3.out", duration: 0.725 });
                } else {
                    mainSlider.previous({
                        ease: "power3.out",
                        duration: 0.725,
                    });
                }
            });
        }

        // Listener para clic en los slides para navegar a ese slide
        slides.forEach((slide, i) => {
            slide.addEventListener("click", (event) => {
                event.stopPropagation();
                pauseAndRestartTimeout();
                const targetLeftSlideIndex = gsap.utils.wrap(
                    0,
                    slideCount,
                    i - 1
                );
                mainSlider.toIndex(targetLeftSlideIndex, {
                    ease: "power3.out",
                    duration: 0.725,
                });
            });
        });

        // Listener para Scroll de Rueda de Ratón
        if (sliderMainArea) {
            sliderMainArea.addEventListener("wheel", (event) => {
                event.preventDefault();
                pauseAndRestartTimeout();

                if (event.deltaY > 0 || event.deltaX > 0) {
                    mainSlider.next({ ease: "power3.out", duration: 0.725 });
                } else if (event.deltaY < 0 || event.deltaX < 0) {
                    mainSlider.previous({
                        ease: "power3.out",
                        duration: 0.725,
                    });
                }
            });
        }

        if (mainSliderConfig.repeat === -1 && !mainSliderConfig.paused) {
            startAutoPlayTimeout();
        }
    }

    });

    // ScrollTrigger (Comentado)
    /*
    if (mainSlider) {
        ScrollTrigger.create({
            trigger: sliderContainer.parentElement,
            start: "top top",
            end: "+=2000",
            scrub: true,
            pin: true,
            animation: mainSlider
        });
    }
    */
