import './bootstrap';

function initHeroSlider() {
    const root = document.querySelector('[data-hero-slider]');
    if (!root) return;

    const slides = Array.from(root.querySelectorAll('[data-slide]'));
    const dots = Array.from(root.querySelectorAll('[data-dot]'));
    if (slides.length <= 1) return;

    let idx = 0;

    const apply = (nextIdx) => {
        idx = ((nextIdx % slides.length) + slides.length) % slides.length;
        slides.forEach((el, i) => el.classList.toggle('is-active', i === idx));
        dots.forEach((el, i) => {
            el.classList.toggle('w-10', i === idx);
            el.classList.toggle('w-2.5', i !== idx);
            el.classList.toggle('bg-white/90', i === idx);
            el.classList.toggle('bg-white/40', i !== idx);
        });
    };

    dots.forEach((dot, i) => dot.addEventListener('click', () => apply(i)));

    apply(0);
    window.setInterval(() => apply(idx + 1), 6500);
}

function initLeftDrawer() {
    const drawer = document.querySelector('[data-left-drawer]');
    if (!drawer) return;

    const panel = drawer.querySelector('[data-left-drawer-panel]');
    const overlay = drawer.querySelector('[data-left-drawer-overlay]');
    const openBtns = Array.from(document.querySelectorAll('[data-left-drawer-open]'));
    const closeBtns = Array.from(drawer.querySelectorAll('[data-left-drawer-close]'));

    const setOpen = (open) => {
        drawer.classList.toggle('is-open', open);
        if (open) {
            document.documentElement.classList.add('overflow-hidden');
            document.body.classList.add('overflow-hidden');
        } else {
            document.documentElement.classList.remove('overflow-hidden');
            document.body.classList.remove('overflow-hidden');
        }
    };

    openBtns.forEach((btn) => btn.addEventListener('click', (e) => {
        e.preventDefault();
        setOpen(true);
    }));
    closeBtns.forEach((btn) => btn.addEventListener('click', (e) => {
        e.preventDefault();
        setOpen(false);
    }));
    overlay?.addEventListener('click', () => setOpen(false));

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') setOpen(false);
    });

    panel?.addEventListener('click', (e) => {
        const el = e.target;
        if (el && el.tagName === 'A') setOpen(false);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initHeroSlider();
    initLeftDrawer();
    initPhoneInputs();
});

function initPhoneInputs() {
    const nodes = document.querySelectorAll('input[name="phone"], input[name="invoice_phone"], input[name="contact_phone"], input[data-phone]');
    nodes.forEach((input) => {
        input.setAttribute('inputmode', 'numeric');
        input.setAttribute('autocomplete', 'tel');
        input.setAttribute('pattern', '[0-9]*');
        input.setAttribute('maxlength', '11');
        const sanitize = () => {
            input.value = String(input.value || '').replace(/\D/g, '').slice(0, 11);
        };
        input.addEventListener('input', sanitize);
        input.addEventListener('blur', sanitize);
        sanitize();
    });
}
