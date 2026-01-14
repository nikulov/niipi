import './bootstrap';
import './swiper.js';

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


