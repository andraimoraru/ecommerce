function storefrontToggleMenu() {
    const menu = document.getElementById('mobileMenu');
    if (!menu) {
        return;
    }

    const button = document.querySelector('[data-menu-toggle]');
    const isOpen = menu.classList.toggle('is-open');
    menu.setAttribute('aria-hidden', isOpen ? 'false' : 'true');

    if (button) {
        button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    }
}

function storefrontToggleBillingCard() {
    const checkbox = document.querySelector('[data-billing-toggle]');
    const billingCard = document.querySelector('[data-billing-card]');

    if (!checkbox || !billingCard) {
        return;
    }

    billingCard.hidden = checkbox.checked;
}

function storefrontBindConfirmForms(root = document) {
    root.querySelectorAll('form[data-confirm]').forEach((form) => {
        if (form.dataset.confirmBound === '1') {
            return;
        }

        form.dataset.confirmBound = '1';
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm || 'Are you sure?')) {
                event.preventDefault();
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-menu-toggle]').forEach((button) => {
        button.addEventListener('click', storefrontToggleMenu);
    });

    document.querySelectorAll('[data-billing-toggle]').forEach((checkbox) => {
        checkbox.addEventListener('change', storefrontToggleBillingCard);
    });

    storefrontToggleBillingCard();
    storefrontBindConfirmForms();
});
