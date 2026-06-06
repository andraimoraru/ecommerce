<?php $account = $data['account'] ?? null; ?>
<?php $addresses = $data['addresses'] ?? []; ?>

<div class="account-layout">
    <aside class="account-sidebar">
        <h2 class="account-sidebar__title">My Account</h2>
        <nav class="account-sidebar__nav">
            <a href="<?= URLROOT ?>/account" class="account-sidebar__link is-active">Overview</a>
            <a href="<?= URLROOT ?>/account/orders" class="account-sidebar__link">Orders</a>
            <a href="<?= URLROOT ?>/products" class="account-sidebar__link">Continue shopping</a>
            <form action="<?= URLROOT ?>/logout" method="POST" class="account-sidebar__form">
                <button type="submit" class="account-sidebar__button">Logout</button>
            </form>
        </nav>
    </aside>

    <section class="account-panel">
        <h1 class="page-title"><?= htmlspecialchars($data['title'] ?? 'My Account') ?></h1>
        <p class="account-panel__lead">
            Welcome back, <?= htmlspecialchars((string)($data['first_name'] ?: 'there')) ?>.
        </p>
        <div class="account-panel__card">
            <h2 class="account-section-title">Account Details</h2>

            <?php if (!$account): ?>
                <p>We could not load your account details right now.</p>
            <?php else: ?>
                <dl class="account-details-list">
                    <div>
                        <dt>Name</dt>
                        <dd><?= htmlspecialchars(trim((string)$account['first_name'] . ' ' . (string)$account['last_name'])) ?></dd>
                    </div>
                    <div>
                        <dt>Email</dt>
                        <dd><?= htmlspecialchars((string)$account['email']) ?></dd>
                    </div>
                    <div>
                        <dt>Phone</dt>
                        <dd><?= htmlspecialchars((string)($account['phone'] ?: 'Not added yet')) ?></dd>
                    </div>
                    <div>
                        <dt>Member Since</dt>
                        <dd><?= htmlspecialchars((string)($account['created_at'] ?? '')) ?></dd>
                    </div>
                </dl>
            <?php endif; ?>
        </div>

        <div class="account-panel__card">
            <h2 class="account-section-title">Saved Addresses</h2>

            <?php if ($addresses === []): ?>
                <p>You do not have any saved addresses yet.</p>
            <?php else: ?>
                <div class="account-address-list">
                    <?php foreach ($addresses as $address): ?>
                        <article class="account-address">
                            <h3><?= htmlspecialchars((string)($address['label'] ?: 'Address')) ?></h3>
                            <p><?= htmlspecialchars((string)$address['first_name']) ?> <?= htmlspecialchars((string)$address['last_name']) ?></p>
                            <p><?= htmlspecialchars((string)$address['line1']) ?></p>
                            <?php if (!empty($address['line2'])): ?><p><?= htmlspecialchars((string)$address['line2']) ?></p><?php endif; ?>
                            <p><?= htmlspecialchars((string)$address['city']) ?><?= !empty($address['region']) ? ', ' . htmlspecialchars((string)$address['region']) : '' ?></p>
                            <p><?= htmlspecialchars((string)$address['postcode']) ?></p>
                            <p><?= htmlspecialchars((string)$address['country_name']) ?></p>
                            <?php if (!empty($address['phone'])): ?><p><?= htmlspecialchars((string)$address['phone']) ?></p><?php endif; ?>

                            <?php if (!empty($address['is_default_shipping']) || !empty($address['is_default_billing'])): ?>
                                <p class="account-address__meta">
                                    <?= !empty($address['is_default_shipping']) ? 'Default shipping' : '' ?>
                                    <?= (!empty($address['is_default_shipping']) && !empty($address['is_default_billing'])) ? ' · ' : '' ?>
                                    <?= !empty($address['is_default_billing']) ? 'Default billing' : '' ?>
                                </p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
