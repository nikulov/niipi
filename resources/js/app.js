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

function initToTopButton() {
    return {
        visible: false,
        offset: 300,

        init() {
            this.checkVisibility();

            window.addEventListener('scroll', () => {
                this.checkVisibility();
            });
        },

        checkVisibility() {
            this.visible = window.scrollY > this.offset;
        },

        scrollTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    };
}

function initToTopThemeBySection() {
    const startDelay = 200;

    function update() {
        const wrap = document.querySelector('.to-top');
        if (!wrap) return;

        const svg = wrap.querySelector('button svg');
        if (!svg) return;

        const rect = svg.getBoundingClientRect();

        const prev = wrap.style.pointerEvents;
        wrap.style.pointerEvents = 'none';

        const el = document.elementFromPoint(
            rect.left + rect.width / 2,
            rect.top + rect.height / 2
        );

        wrap.style.pointerEvents = prev;

        const section = el?.closest('.to-top-dark, .to-top-white');
        if (!section) return;

        svg.classList.toggle('text-primary', section.classList.contains('to-top-dark'));
        svg.classList.toggle('text-white', section.classList.contains('to-top-white'));
    }

    function start() {
        setTimeout(() => {
            update();
            window.addEventListener('scroll', update);
            window.addEventListener('resize', update);
        }, startDelay);
    }

    if (document.readyState === 'complete') {
        start();
        return;
    }

    window.addEventListener('load', start);
}

function themeToggle() {
    return {
        isDark: document.documentElement.classList.contains('dark'),

        toggle() {
            window.dispatchEvent(new CustomEvent('theme-fade'));

            const root = document.documentElement;
            const willBeDark = !root.classList.contains('dark');

            window.setTimeout(() => {
                root.classList.toggle('dark', willBeDark);
                localStorage.theme = willBeDark ? 'dark' : 'light';

                this.isDark = willBeDark;
            }, 90);
        },
    };
}

document.addEventListener('alpine:init', () => {
    Alpine.data('imageGalleryModal', imageGalleryModal);
    Alpine.data('initToTopButton', initToTopButton);
    Alpine.data('themeToggle', themeToggle)

    initToTopThemeBySection()
});


