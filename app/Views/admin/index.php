<section class="admin-dashboard-hero">
    <p class="admin-kicker">Dashboard</p>
    <h1 class="admin-dashboard-title">Admin dashboard</h1>
    <p class="admin-dashboard-copy">
        Manage the store, orders, customers, content and marketing from one place.
    </p>
</section>

<div class="admin-page-actions admin-dashboard-actions">
    <a href="<?= URLROOT ?>/admin/products/create" class="btn">
        + Add Product
    </a>

    <a href="<?= URLROOT ?>/admin/categories/create" class="btn secondary">
        + Add Category
    </a>
</div>

<div class="admin-dashboard-grid" aria-label="Admin quick links">
    <a href="<?= URLROOT ?>/admin/products" class="admin-dashboard-card">
        <span class="admin-dashboard-card__label">Products</span>
        <span class="admin-dashboard-card__text">Create, edit and review your product catalogue.</span>
    </a>

    <a href="<?= URLROOT ?>/admin/categories" class="admin-dashboard-card">
        <span class="admin-dashboard-card__label">Categories</span>
        <span class="admin-dashboard-card__text">Organise storefront categories and category images.</span>
    </a>

    <a href="<?= URLROOT ?>/admin/orders" class="admin-dashboard-card">
        <span class="admin-dashboard-card__label">Orders</span>
        <span class="admin-dashboard-card__text">View orders, update order items and create Royal Mail orders.</span>
    </a>

    <a href="<?= URLROOT ?>/admin/customers" class="admin-dashboard-card">
        <span class="admin-dashboard-card__label">Customers</span>
        <span class="admin-dashboard-card__text">See customer details, saved addresses and order history.</span>
    </a>

    <a href="<?= URLROOT ?>/admin/marketing" class="admin-dashboard-card">
        <span class="admin-dashboard-card__label">Marketing</span>
        <span class="admin-dashboard-card__text">Manage social profile links and integration settings.</span>
    </a>

    <a href="<?= URLROOT ?>/admin/blog" class="admin-dashboard-card">
        <span class="admin-dashboard-card__label">Blog</span>
        <span class="admin-dashboard-card__text">Draft, publish and manage blog posts.</span>
    </a>
</div>
