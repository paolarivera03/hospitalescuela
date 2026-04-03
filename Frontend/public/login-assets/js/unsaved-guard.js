(function () {
    'use strict';

    const trackedForms = Array.from(document.querySelectorAll('form')).filter((form) => {
        const methodAttr = form.getAttribute('method');
        const method = methodAttr ? methodAttr.toUpperCase() : '';
        if (method === 'GET') return false;
        if (form.hasAttribute('data-unsaved-ignore')) return false;
        return true;
    });

    if (!trackedForms.length) return;

    let allowNavigation = false;

    const hasPendingChanges = () => trackedForms.some((form) => form.dataset.unsavedDirty === '1');

    const markDirty = (event) => {
        const form = event.target.closest('form');
        if (!form || !trackedForms.includes(form)) return;
        form.dataset.unsavedDirty = '1';
    };

    const askDiscard = () => window.confirm('¡Cuidado! Los datos no se guardarán.');

    trackedForms.forEach((form) => {
        form.dataset.unsavedDirty = '0';
        form.addEventListener('input', markDirty);
        form.addEventListener('change', markDirty);
        form.addEventListener('submit', () => {
            allowNavigation = true;
            form.dataset.unsavedDirty = '0';
        });
    });

    document.addEventListener('click', function (event) {
        const link = event.target.closest('a[href]');
        if (!link) return;
        if (link.target === '_blank') return;
        if (link.getAttribute('href') === '#') return;
        if (!hasPendingChanges() || allowNavigation) return;

        event.preventDefault();

        if (askDiscard()) {
            allowNavigation = true;
            window.location.href = link.href;
        }
    }, true);

    window.addEventListener('beforeunload', function (event) {
        if (!hasPendingChanges() || allowNavigation) return;
        event.preventDefault();
        event.returnValue = '';
    });

    history.pushState({ unsavedGuardAuth: true }, '', window.location.href);

    window.addEventListener('popstate', function () {
        if (!hasPendingChanges() || allowNavigation) return;

        history.pushState({ unsavedGuardAuth: true }, '', window.location.href);

        if (askDiscard()) {
            allowNavigation = true;
            history.back();
        }
    });
})();
