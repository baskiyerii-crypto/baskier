import './bootstrap';

function initModals() {
    let activeModal = null;
    let returnFocus = null;

    const getFocusable = (el) => [...el.querySelectorAll('a[href], button, input, select, textarea, [tabindex="0"]')]
        .filter((node) => !node.disabled && node.getClientRects().length);

    window.openModal = function(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        activeModal = modal;
        returnFocus = document.activeElement;

        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100');
        const box = modal.querySelector('.modal-box');
        if (box) {
            box.classList.remove('scale-95');
            box.classList.add('scale-100');
        }
        document.documentElement.classList.add('overflow-hidden');
        document.body.classList.add('overflow-hidden');

        setTimeout(() => {
            const focusable = getFocusable(modal);
            if (focusable.length) focusable[0].focus();
        }, 50);
    };

    window.closeModal = function(id) {
        const modal = id ? document.getElementById(id) : activeModal;
        if (!modal) return;

        modal.classList.add('opacity-0', 'pointer-events-none');
        modal.classList.remove('opacity-100');
        const box = modal.querySelector('.modal-box');
        if (box) {
            box.classList.add('scale-95');
            box.classList.remove('scale-100');
        }
        document.documentElement.classList.remove('overflow-hidden');
        document.body.classList.remove('overflow-hidden');

        if (returnFocus) {
            returnFocus.focus();
            returnFocus = null;
        }
        if (activeModal === modal) activeModal = null;
    };

    document.addEventListener('click', (e) => {
        const openBtn = e.target.closest('[data-modal-open]');
        if (openBtn) {
            const targetId = openBtn.getAttribute('data-modal-open');
            window.openModal(targetId);
            return;
        }

        const closeBtn = e.target.closest('[data-modal-close]');
        if (closeBtn) {
            const modal = closeBtn.closest('[data-modal]');
            if (modal) window.closeModal(modal.id);
        }
    });

    document.addEventListener('keydown', (e) => {
        if (!activeModal) return;
        if (e.key === 'Escape') {
            window.closeModal(activeModal.id);
            return;
        }
        if (e.key === 'Tab') {
            const nodes = getFocusable(activeModal);
            if (nodes.length === 0) return;
            const first = nodes[0];
            const last = nodes[nodes.length - 1];
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }
    });
}

function initDrawers() {
    let activeDrawer = null;
    let returnFocus = null;

    window.openDrawer = function(id) {
        const drawer = document.getElementById(id);
        if (!drawer) return;
        activeDrawer = drawer;
        returnFocus = document.activeElement;

        drawer.classList.remove('opacity-0', 'pointer-events-none');
        drawer.classList.add('opacity-100');
        const panel = drawer.querySelector('.drawer-panel');
        if (panel) {
            panel.classList.remove('-translate-x-full', 'translate-x-full');
            panel.classList.add('translate-x-0');
        }
        document.documentElement.classList.add('overflow-hidden');
        document.body.classList.add('overflow-hidden');
    };

    window.closeDrawer = function(id) {
        const drawer = id ? document.getElementById(id) : activeDrawer;
        if (!drawer) return;

        drawer.classList.add('opacity-0', 'pointer-events-none');
        drawer.classList.remove('opacity-100');
        const panel = drawer.querySelector('.drawer-panel');
        if (panel) {
            const side = drawer.getAttribute('data-side') || 'left';
            panel.classList.remove('translate-x-0');
            panel.classList.add(side === 'left' ? '-translate-x-full' : 'translate-x-full');
        }
        document.documentElement.classList.remove('overflow-hidden');
        document.body.classList.remove('overflow-hidden');

        if (returnFocus) {
            returnFocus.focus();
            returnFocus = null;
        }
        if (activeDrawer === drawer) activeDrawer = null;
    };

    document.addEventListener('click', (e) => {
        const openBtn = e.target.closest('[data-drawer-open]');
        if (openBtn) {
            const targetId = openBtn.getAttribute('data-drawer-open');
            window.openDrawer(targetId);
            return;
        }

        const closeBtn = e.target.closest('[data-drawer-close]');
        if (closeBtn) {
            const drawer = closeBtn.closest('[data-drawer]');
            if (drawer) window.closeDrawer(drawer.id);
        }
    });

    document.addEventListener('keydown', (e) => {
        if (!activeDrawer) return;
        if (e.key === 'Escape') {
            window.closeDrawer(activeDrawer.id);
        }
    });
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

document.addEventListener('DOMContentLoaded', () => {
    initModals();
    initDrawers();
    initLeftDrawer();
    initPhoneInputs();
    document.querySelector('[data-validation-summary]')?.focus();
});
