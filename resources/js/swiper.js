import Swiper from "swiper";
import {Autoplay, Navigation, Pagination,} from "swiper/modules";

document.addEventListener('DOMContentLoaded', initSlider);
document.addEventListener('turbo:load', initSlider);

function initSlider()
{
    const sliderEl = document.querySelector('.js-main-slider');
    if (!sliderEl) return;

    const slider = new Swiper(sliderEl, {
        modules: [Navigation, Pagination, Autoplay,],
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