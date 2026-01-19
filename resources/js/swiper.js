import Swiper from "swiper";
import {Autoplay, FreeMode, Navigation, Pagination, Thumbs, Keyboard} from "swiper/modules";

document.addEventListener('DOMContentLoaded', initMainSlider);
document.addEventListener('turbo:load', initMainSlider);


function initMainSlider()
{
    const sliderEl = document.querySelector('.js-main-slider');
    if (!sliderEl) return;

    const slider = new Swiper(sliderEl, {
        modules: [Navigation, Pagination, Autoplay],
        direction: 'horizontal',
        loop: true,
        speed: 1000,
        autoHeight: true,
        autoplay: {
            delay: 2000,
        },
        slidesPerView: 1,
        spaceBetween: 0,
        navigation: {
            nextEl: '.js-main-slider-next',
            prevEl: '.js-main-slider-prev',
        },
        pagination: {
            el: '.js-main-slider-pagination',
            clickable: true,
        },
    });
}

/* Function */
function initGallerySliderForRoot(rootEl, startIndex = 0) {
    if (!rootEl) return;

    const sliderEl = rootEl.querySelector(".js-gallery-slider");
    const thumbsEl = rootEl.querySelector(".js-gallery-slider-thumbs");
    if (!sliderEl || !thumbsEl) return;

    if (sliderEl.__swiper) {
        sliderEl.__swiper.destroy(true, true);
        sliderEl.__swiper = null;
    }
    if (thumbsEl.__swiper) {
        thumbsEl.__swiper.destroy(true, true);
        thumbsEl.__swiper = null;
    }

    const thumbsSwiper = new Swiper(thumbsEl, {
        modules: [FreeMode, Thumbs],
        loop: false,
        direction: "horizontal",
        spaceBetween: 10,
        slidesPerView: "auto",
        freeMode: true,
        watchSlidesProgress: true,
    });

    const slider = new Swiper(sliderEl, {
        modules: [Navigation, Pagination, Thumbs, Keyboard],
        loop: true,
        speed: 250,
        slidesPerView: 1,
        spaceBetween: 10,

        navigation: {
            nextEl: rootEl.querySelector(".js-gallery-slider-next"),
            prevEl: rootEl.querySelector(".js-gallery-slider-prev"),
        },

        thumbs: {
            swiper: thumbsSwiper,
        },

        keyboard: {
            enabled: true,
            onlyInViewport: false,
        },
    });

    sliderEl.__swiper = slider;
    thumbsEl.__swiper = thumbsSwiper;

    const goTo = (i) => {
        const idx = Number(i) || 0;
        slider.slideToLoop(idx, 0);
        thumbsSwiper.slideTo(Math.max(idx - 2, 0), 0);
    };

    goTo(startIndex);
}

window.initGallerySliderForRoot = initGallerySliderForRoot;