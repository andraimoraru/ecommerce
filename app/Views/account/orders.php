<div class="account-layout">
    <aside class="account-sidebar">
        <h2 class="account-sidebar__title">My Account</h2>
        <nav class="account-sidebar__nav">
            <a href="<?= URLROOT ?>/account" class="account-sidebar__link">Overview</a>
            <a href="<?= URLROOT ?>/account/orders" class="account-sidebar__link is-active">Orders</a>
            <a href="<?= URLROOT ?>/products" class="account-sidebar__link">Continue shopping</a>
            <form action="<?= URLROOT ?>/logout" method="POST" class="account-sidebar__form">
                <button type="submit" class="account-sidebar__button">Logout</button>
            </form>
        </nav>
    </aside>

    <section class="account-panel">
        <h1 class="page-title"><?= htmlspecialchars((string)($data['title'] ?? 'My Orders')) ?></h1>

        <?php $orders = $data['orders'] ?? []; ?>

        <?php if ($orders === []): ?>
            <div class="account-panel__card">
                <p>You do not have any orders yet.</p>
                <p><a href="<?= URLROOT ?>/products">Start shopping</a></p>
            </div>
        <?php else: ?>
            <div class="account-panel__card">
                <table class="account-order-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Placed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>
                                    <a href="<?= URLROOT ?>/account/orders/<?= (int)$order['id'] ?>">
                                        <?= htmlspecialchars((string)$order['order_number']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars((string)$order['status']) ?></td>
                                <td>
                                    <?= htmlspecialchars((string)$order['currency']) ?>
                                    <?= number_format(((int)$order['total_minor']) / 100, 2) ?>
                                </td>
                                <td><?= htmlspecialchars((string)($order['placed_at'] ?? $order['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>
