<div class="account-layout">
    <aside class="account-sidebar">
        <h2 class="account-sidebar__title">My Account</h2>
        <nav class="account-sidebar__nav">
            <a href="<?= URLROOT ?>/account" class="account-sidebar__link is-active">Overview</a>
            <a href="<?= URLROOT ?>/products" class="account-sidebar__link">Continue shopping</a>
            <form action="<?= URLROOT ?>/logout" method="POST" class="account-sidebar__form">
                <button type="submit" class="account-sidebar__button">Logout</button>
            </form>
        </nav>
    </aside>

    <section class="account-panel">
        <h1 class="page-title"><?= htmlspecialchars($data['title'] ?? 'My Account') ?></h1>
        <p class="account-panel__lead">Welcome back.</p>
        <div class="account-panel__card">
            <p>You can manage your account from here and continue browsing the store whenever you like.</p>
        </div>
    </section>
</div>
