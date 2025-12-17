import './bootstrap';
import './swiper.js';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import intersect from '@alpinejs/intersect';
import persist from '@alpinejs/persist';
import mask from '@alpinejs/mask';
import focus from '@alpinejs/focus';
import morph from '@alpinejs/morph';

Alpine.plugin(collapse);
Alpine.plugin(intersect);
Alpine.plugin(persist);
Alpine.plugin(mask);
Alpine.plugin(focus);
Alpine.plugin(morph);

window.Alpine = Alpine;
Alpine.start();


function imageGalleryModal() {
    return {
        isOpen: false,
        currentSrc: '',
        currentAlt: '',

        init() {
            this.$watch('isOpen', value => {
                document.body.classList.toggle('overflow-hidden', value);
            });

            document.addEventListener('keydown', event => {
                if (event.key === 'Escape' && this.isOpen) {
                    this.close();
                }
            });
        },

        show(src, alt = '') {
            this.currentSrc = src;
            this.currentAlt = alt;
            this.isOpen = true;
        },

        close() {
            this.isOpen = false;
            this.currentSrc = '';
            this.currentAlt = '';
        }
    }
}

document.addEventListener('alpine:init', () => {
    Alpine.data('imageGalleryModal', imageGalleryModal);
});


