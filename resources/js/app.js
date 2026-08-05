import './bootstrap';
import './swiper.js';

function imageGalleryModal() {
    return {
        isOpen: false,
        currentSrc: '',
        currentAlt: '',

        init() {
            this.$watch('isOpen', (value) => {
                document.body.classList.toggle('overflow-hidden', value);
            });

            document.addEventListener('keydown', (event) => {
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
        },
    };
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
                behavior: 'smooth',
            });
        },
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

        const el = document.elementFromPoint(rect.left + rect.width / 2, rect.top + rect.height / 2);

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
                localStorage.themeSite = willBeDark ? 'dark' : 'light';

                this.isDark = willBeDark;
            }, 90);
        },
    };
}

function phoneMask(model) {
    const groups = [3, 3, 2, 2];

    const digitsOf = (value) => value.replace(/\D/g, '');

    // leading 7/8 is the country code; any other first digit means the code is missing
    function normalize(digits) {
        if (digits === '') return '';

        const rest = /^[78]/.test(digits) ? digits.slice(1) : digits;

        return ('7' + rest).slice(0, 11);
    }

    function format(digits) {
        if (digits === '') return '';

        let out = '+7';
        let at = 1;

        for (const [index, size] of groups.entries()) {
            if (at >= digits.length) break;

            const part = digits.slice(at, at + size);

            // the area code goes in parentheses, closed once it is complete
            out += index === 0 ? ' (' + part + (part.length === size ? ')' : '') : ' ' + part;

            at += size;
        }

        return out;
    }

    function caretAt(value, digitCount) {
        if (digitCount < 1) return value === '' ? 0 : 2;

        let seen = 0;

        for (let i = 0; i < value.length; i++) {
            if (/\d/.test(value[i]) && ++seen === digitCount) return i + 1;
        }

        return value.length;
    }

    return {
        onInput(el) {
            const raw = el.value;
            const rawDigits = digitsOf(raw);
            const caretDigits = digitsOf(raw.slice(0, el.selectionStart ?? raw.length)).length;

            // normalization may prepend the country code, shifting the caret one digit right
            const shift = rawDigits !== '' && !/^[78]/.test(rawDigits) ? 1 : 0;

            const value = format(normalize(rawDigits));

            el.value = value;

            const caret = caretAt(value, caretDigits + shift);
            el.setSelectionRange(caret, caret);

            this.push(value);
        },

        onFocus(el) {
            if (el.value !== '') return;

            el.value = '+7 ';
            el.setSelectionRange(3, 3);
        },

        onBlur(el) {
            // a bare country code must not reach validation of an optional field
            if (digitsOf(el.value).length > 1) return;

            el.value = '';
            this.push('');
        },

        // Backspace must delete a digit, not a separator the mask immediately restores —
        // ") " is two of them in a row. The "+7" prefix is fixed, so deleting into it is a no-op
        onBackspace(event) {
            const el = event.target;
            let pos = el.selectionStart;

            if (pos === null || pos !== el.selectionEnd) return;

            while (pos > 0 && !/\d/.test(el.value[pos - 1])) pos--;

            if (pos <= 2) {
                event.preventDefault();

                return;
            }

            el.setSelectionRange(pos, pos);
        },

        // wire:model listens to the same input event and listener order is not guaranteed,
        // so write to the component explicitly; false = local only, no server request
        push(value) {
            this.$wire.set(model, value, false);
        },
    };
}

document.addEventListener('alpine:init', () => {
    Alpine.data('imageGalleryModal', imageGalleryModal);
    Alpine.data('initToTopButton', initToTopButton);
    Alpine.data('themeToggle', themeToggle);
    Alpine.data('phoneMask', phoneMask);

    initToTopThemeBySection();
});
