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
            <a class="add-cart-btn" href="<?= URLROOT ?>/checkout" style="display:block;text-align:center;text-decoration:none;">
                Return to checkout
            </a>
            <p style="margin-top:12px;">
                <a href="<?= URLROOT ?>/cart">Back to cart</a>
            </p>
        </div>
    </div>
</div>
