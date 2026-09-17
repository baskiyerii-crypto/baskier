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
    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        window.setInterval(() => {
            if (!document.hidden && !root.matches(':hover, :focus-within')) apply(idx + 1);
        }, 6500);
    }
}

function initNavigationDrawer({ panel, overlay, openButtons, closeButtons, root, mobileOnly = false }) {
    if (!panel) return;
    const media = window.matchMedia('(max-width: 991.98px)');
    let opened = false;
    let returnFocus = null;
    const focusable = () => [...panel.querySelectorAll('a[href], button, input, select, textarea, [tabindex="0"]')]
        .filter((node) => !node.disabled && node.getClientRects().length);
    const setOpen = (next, restore = true) => {
        opened = next;
        const hidden = !opened && (!mobileOnly || media.matches);
        panel.inert = hidden;
        panel.setAttribute('aria-hidden', String(hidden));
        if (root) {
            root.inert = hidden;
            root.setAttribute('aria-hidden', String(hidden));
            root.classList.toggle('is-open', opened);
        }
        panel.classList.toggle('show', opened);
        overlay?.classList.toggle('show', opened);
        openButtons.forEach((button) => button.setAttribute('aria-expanded', String(opened)));
        document.documentElement.classList.toggle('overflow-hidden', opened);
        document.body.classList.toggle('overflow-hidden', opened);
        if (opened) {
            returnFocus = document.activeElement;
            (focusable()[0] || panel).focus();
        } else if (restore && returnFocus) {
            returnFocus.focus();
            returnFocus = null;
        }
    };
    openButtons.forEach((button) => button.addEventListener('click', () => setOpen(true)));
    closeButtons.forEach((button) => button.addEventListener('click', () => setOpen(false)));
    overlay?.addEventListener('click', () => setOpen(false));
    document.addEventListener('keydown', (event) => {
        if (!opened) return;
        if (event.key === 'Escape') setOpen(false);
        if (event.key === 'Tab') {
            const nodes = focusable();
            const first = nodes[0] || panel;
            const last = nodes[nodes.length - 1] || panel;
            if (event.shiftKey && (document.activeElement === first || document.activeElement === panel)) {
                event.preventDefault(); last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault(); first.focus();
            }
        }
    });
    panel.addEventListener('click', (event) => {
        if (event.target.closest('a[href]') && opened) setOpen(false);
    });
    media.addEventListener('change', () => setOpen(false, false));
    setOpen(false, false);
}

function initLeftDrawer() {
    initNavigationDrawer({
        panel: document.querySelector('[data-panel-sidebar]'),
        overlay: document.querySelector('[data-panel-backdrop]'),
        openButtons: [...document.querySelectorAll('[data-panel-open]')],
        closeButtons: [...document.querySelectorAll('[data-panel-close]')],
        mobileOnly: true,
    });
    document.querySelectorAll('nav a.active').forEach((link) => link.setAttribute('aria-current', 'page'));
}

document.addEventListener('DOMContentLoaded', () => {
    initHeroSlider();
    initLeftDrawer();
    initPhoneInputs();
    document.querySelector('[data-validation-summary]')?.focus();
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
