<h1><?= htmlspecialchars($data['title'] ?? 'Payment cancelled') ?></h1>

<div class="cart-layout">
    <div class="cart-main">
        <div class="cart-card">
            <h2>Payment cancelled</h2>
            <p>Your order was not paid, and your cart has been left unchanged.</p>
            <p>You can return to checkout and try again whenever you're ready.</p>
        </div>
    </div>

    <div class="cart-sidebar">
        <div class="cart-card">
            <a class="add-cart-btn button-link button-link--block" href="<?= URLROOT ?>/checkout">
                Return to checkout
            </a>
            <p class="stack-sm">
                <a href="<?= URLROOT ?>/cart">Back to cart</a>
            </p>
        </div>
    </div>
</div>
