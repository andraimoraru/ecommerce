function adminToggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (!sidebar) {
        return;
    }

    sidebar.classList.toggle('show');
}

function adminBindConfirmForms(root = document) {
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

function adminEscapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function adminRenderSkuSearchResults(query, products) {
    const results = document.getElementById('skuSearchResults');
    if (!results) {
        return;
    }

    const normalized = query.trim().toLowerCase();
    if (normalized === '') {
        results.innerHTML = '';
        return;
    }

    const matches = products
        .filter((product) => {
            const sku = String(product.sku || '').toLowerCase();
            const name = String(product.name || '').toLowerCase();
            return sku.includes(normalized) || name.includes(normalized);
        })
        .slice(0, 8);

    if (matches.length === 0) {
        results.innerHTML = '<p class="admin-note">No matching products found.</p>';
        return;
    }

    results.innerHTML = matches.map((product) => {
        const thumbnail = product.primary_image
            ? `<img src="${adminEscapeHtml(product.primary_image)}" alt="" class="admin-thumb admin-thumb-placeholder--small">`
            : '<div class="admin-thumb-placeholder admin-thumb-placeholder--small">No image</div>';

        return `
            <div class="admin-sku-result">
                <div>${thumbnail}</div>
                <div>
                    <strong>${adminEscapeHtml(product.name || '')}</strong><br>
                    <small class="admin-meta">${adminEscapeHtml(product.sku || '')}</small>
                </div>
                <div>
                    <input type="number" min="1" value="1" data-qty-for="${product.id}" class="admin-qty-input admin-qty-input--small">
                </div>
                <button class="btn secondary" type="button" data-add-product-id="${Number(product.id)}">Add</button>
            </div>
        `;
    }).join('');
}

function adminAddSearchedProduct(productId, products) {
    const product = products.find((item) => Number(item.id) === Number(productId));
    const qtyInput = document.querySelector(`[data-qty-for="${productId}"]`);
    const quantity = qtyInput ? Number(qtyInput.value || 0) : 0;
    const container = document.getElementById('pendingAdditions');

    if (!product || !container || quantity <= 0) {
        return;
    }

    const row = document.createElement('div');
    row.className = 'admin-sku-pending-row';

    const thumbnail = product.primary_image
        ? `<img src="${adminEscapeHtml(product.primary_image)}" alt="" class="admin-thumb admin-thumb-placeholder--small">`
        : '<div class="admin-thumb-placeholder admin-thumb-placeholder--small">No image</div>';

    row.innerHTML = `
        <div>${thumbnail}</div>
        <div>
            <strong>${adminEscapeHtml(product.name || '')}</strong><br>
            <small class="admin-meta">${adminEscapeHtml(product.sku || '')}</small>
            <input type="hidden" name="new_product_id[]" value="${Number(product.id)}">
        </div>
        <div>
            <input type="number" min="1" name="new_quantity[]" value="${quantity}" class="admin-qty-input admin-qty-input--small">
        </div>
        <button class="btn secondary" type="button" data-remove-pending>Remove</button>
    `;

    row.querySelector('[data-remove-pending]')?.addEventListener('click', () => row.remove());
    container.appendChild(row);
}

function adminInitOrderEditor() {
    const app = document.querySelector('[data-order-editor-products]');
    const search = document.getElementById('skuSearch');

    if (!app || !search) {
        return;
    }

    let products = [];

    try {
        products = JSON.parse(app.getAttribute('data-order-editor-products') || '[]');
    } catch (error) {
        console.error('Failed to parse order editor products.', error);
        return;
    }

    search.addEventListener('input', (event) => {
        adminRenderSkuSearchResults(event.target.value || '', products);
    });

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-add-product-id]');
        if (!trigger) {
            return;
        }

        adminAddSearchedProduct(Number(trigger.getAttribute('data-add-product-id')), products);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
        button.addEventListener('click', adminToggleSidebar);
    });

    adminBindConfirmForms();
    adminInitOrderEditor();
});
