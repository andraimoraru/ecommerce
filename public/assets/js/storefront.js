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

function storefrontNormalizeCountry(country) {
    return String(country || '').trim().toUpperCase();
}

function storefrontShippingMinorForCountry(country) {
    const normalized = storefrontNormalizeCountry(country);

    if (normalized === '') {
        return null;
    }

    if (['UK', 'UNITED KINGDOM', 'GB', 'GREAT BRITAIN'].includes(normalized)) {
        return 299;
    }

    return 1099;
}

function storefrontFormatGbp(minor) {
    return `GBP ${(minor / 100).toFixed(2)}`;
}

function storefrontBindCheckoutShippingEstimate() {
    const countryInput = document.querySelector('[data-shipping-country]');
    const subtotalNode = document.querySelector('[data-checkout-subtotal-minor]');
    const shippingNode = document.querySelector('[data-checkout-shipping]');
    const totalNode = document.querySelector('[data-checkout-total]');
    const noteNode = document.querySelector('[data-checkout-shipping-note]');

    if (!countryInput || !subtotalNode || !shippingNode || !totalNode || !noteNode) {
        return;
    }

    const subtotalMinor = Number(subtotalNode.dataset.checkoutSubtotalMinor || '0');

    const render = () => {
        const shippingMinor = storefrontShippingMinorForCountry(countryInput.value);

        if (shippingMinor === null) {
            shippingNode.textContent = 'Enter shipping country';
            totalNode.textContent = storefrontFormatGbp(subtotalMinor);
            noteNode.textContent = 'UK delivery is GBP 2.99. International delivery is GBP 10.99.';
            return;
        }

        shippingNode.textContent = storefrontFormatGbp(shippingMinor);
        totalNode.textContent = storefrontFormatGbp(subtotalMinor + shippingMinor);
        noteNode.textContent = shippingMinor === 299
            ? 'UK delivery rate applied.'
            : 'International delivery rate applied.';
    };

    countryInput.addEventListener('input', render);
    countryInput.addEventListener('change', render);
    render();
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
    storefrontBindCheckoutShippingEstimate();
});
