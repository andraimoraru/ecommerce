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
        button.setAttribute('aria-label', isOpen ? 'Close menu' : 'Open menu');
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
    const modal = storefrontCreateConfirmModal();

    root.querySelectorAll('form[data-confirm]').forEach((form) => {
        if (form.dataset.confirmBound === '1') {
            return;
        }

        form.dataset.confirmBound = '1';
        form.addEventListener('submit', (event) => {
            if (form.dataset.confirmApproved === '1') {
                delete form.dataset.confirmApproved;
                return;
            }

            event.preventDefault();
            storefrontOpenConfirmModal(modal, form);
        });
    });
}

function storefrontCreateConfirmModal() {
    let modal = document.querySelector('[data-confirm-modal]');

    if (modal) {
        return modal;
    }

    modal = document.createElement('div');
    modal.className = 'confirm-modal';
    modal.hidden = true;
    modal.dataset.confirmModal = '1';
    modal.innerHTML = `
        <div class="confirm-modal__overlay" data-confirm-cancel></div>
        <section
            class="confirm-modal__dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="confirmModalTitle"
            aria-describedby="confirmModalMessage"
        >
            <p class="confirm-modal__eyebrow">Cart update</p>
            <h2 id="confirmModalTitle" class="confirm-modal__title">Remove item?</h2>
            <p id="confirmModalMessage" class="confirm-modal__message"></p>
            <div class="confirm-modal__actions">
                <button type="button" class="btn secondary confirm-modal__button" data-confirm-cancel>Keep item</button>
                <button type="button" class="btn danger confirm-modal__button" data-confirm-approve>Remove item</button>
            </div>
        </section>
    `;

    document.body.appendChild(modal);

    modal.querySelectorAll('[data-confirm-cancel]').forEach((button) => {
        button.addEventListener('click', () => storefrontCloseConfirmModal(modal));
    });

    modal.querySelector('[data-confirm-approve]')?.addEventListener('click', () => {
        const form = modal.confirmForm;

        if (!form) {
            storefrontCloseConfirmModal(modal);
            return;
        }

        form.dataset.confirmApproved = '1';
        storefrontCloseConfirmModal(modal);
        form.requestSubmit();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) {
            storefrontCloseConfirmModal(modal);
        }
    });

    return modal;
}

function storefrontOpenConfirmModal(modal, form) {
    const message = form.dataset.confirm || 'Are you sure you want to continue?';
    const messageNode = modal.querySelector('#confirmModalMessage');

    if (messageNode) {
        messageNode.textContent = message;
    }

    modal.confirmForm = form;
    modal.previousFocus = document.activeElement;
    modal.hidden = false;
    document.body.classList.add('has-confirm-modal');
    modal.querySelector('[data-confirm-cancel]')?.focus();
}

function storefrontCloseConfirmModal(modal) {
    modal.hidden = true;
    document.body.classList.remove('has-confirm-modal');

    if (modal.previousFocus && typeof modal.previousFocus.focus === 'function') {
        modal.previousFocus.focus();
    }

    modal.confirmForm = null;
    modal.previousFocus = null;
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

function storefrontBindProductGallery() {
    const mainImage = document.querySelector('[data-product-main-image]');
    const thumbnails = document.querySelectorAll('[data-product-thumb]');

    if (!mainImage || thumbnails.length === 0) {
        return;
    }

    thumbnails.forEach((thumbnail) => {
        thumbnail.addEventListener('click', () => {
            const imageSrc = thumbnail.dataset.imageSrc || '';
            const imageAlt = thumbnail.dataset.imageAlt || '';

            if (imageSrc === '') {
                return;
            }

            mainImage.src = imageSrc;
            mainImage.alt = imageAlt;

            thumbnails.forEach((item) => {
                const isSelected = item === thumbnail;
                item.classList.toggle('is-active', isSelected);
                item.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
            });
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
    storefrontBindCheckoutShippingEstimate();
    storefrontBindProductGallery();
});
