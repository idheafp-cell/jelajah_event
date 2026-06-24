import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const navToggle = document.querySelector('[data-nav-toggle]');
    const navMenu = document.querySelector('[data-nav-menu]');

    navToggle?.addEventListener('click', () => {
        const isOpen = navMenu.classList.toggle('open');
        navToggle.setAttribute('aria-expanded', String(isOpen));
    });

    document.querySelectorAll('[data-dismiss]').forEach(button => {
        button.addEventListener('click', () => button.closest('.flash')?.remove());
    });

    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', event => {
            if (!window.confirm(form.dataset.confirm)) event.preventDefault();
        });
    });

    document.querySelectorAll('[data-fill-login]').forEach(button => {
        button.addEventListener('click', () => {
            const form = button.closest('.login-form-wrap');
            form.querySelector('input[name="email"]').value = button.dataset.email;
            form.querySelector('input[name="password"]').value = 'password';
        });
    });

    const bindDateRange = (startName, endName) => {
        document.querySelectorAll(`input[name="${startName}"]`).forEach(startInput => {
            const form = startInput.closest('form');
            const endInput = form?.querySelector(`input[name="${endName}"]`);
            if (!endInput) return;

            const syncMinimumDate = () => {
                endInput.min = startInput.value;
                if (endInput.value && startInput.value && endInput.value < startInput.value) {
                    endInput.value = startInput.value;
                }
            };

            startInput.addEventListener('change', syncMinimumDate);
            syncMinimumDate();
        });
    };

    bindDateRange('date_from', 'date_to');
    bindDateRange('start_date', 'end_date');
});
