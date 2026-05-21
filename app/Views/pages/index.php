<?php $products = $data['products'] ?? []; ?>
<?php $categories = $data['categories'] ?? []; ?>
<?php
$heroPrimary = $products[1]['primary_image'] ?? '';
$heroSecondary = $products[2]['primary_image'] ?? $heroPrimary;
$heroTertiary = $products[3]['primary_image'] ?? $heroSecondary;
$ambassadorVisual = $products[4]['primary_image'] ?? $heroPrimary;
$testimonials = [
    [
        'quote' => 'Beautiful pieces, quick delivery, and that polished gold finish you want to wear every day.',
        'author' => 'Verified customer',
    ],
    [
        'quote' => 'Elegant without feeling overdone. It has that giftable, feminine look that makes the whole storefront feel premium.',
        'author' => 'Recent shopper',
    ],
    [
        'quote' => 'A warm, luxurious presentation that makes even affordable jewellery feel special from the first click.',
        'author' => 'Store visitor',
    ],
];
?>

<div class="home-page">
    <section class="home-hero">
        <div class="home-hero__content">
            <p class="eyebrow">The joy of gifting</p>
            <h1 class="home-hero__title">Elegant gold plated jewellery for everyday shine.</h1>
            <p class="home-hero__copy">
                Discover delicate necklaces, polished rings, gift-worthy sets, and statement pieces designed to bring a soft luxury feel to every order.
            </p>

            <div class="home-hero__actions">
                <a class="btn" href="<?= URLROOT ?>/products">Shop the collection</a>
                <a class="btn secondary" href="<?= URLROOT ?>/categories">Browse categories</a>
            </div>

            <div class="home-hero__points">
                <div class="home-hero__point">
                    <strong>Gift-first feel</strong>
                    <span>Warm tones, elegant styling, and products that feel ready to give.</span>
                </div>
                <div class="home-hero__point">
                    <strong>Affordable polish</strong>
                    <span>Jewellery that looks elevated without losing the accessible, everyday appeal.</span>
                </div>
                <div class="home-hero__point">
                    <strong>Fast discovery</strong>
                    <span>Shop by category, jump into new arrivals, and move quickly from browse to checkout.</span>
                </div>
            </div>
        </div>

        <div class="home-hero__visual">
            <div class="home-hero__badge">New collection</div>
            <div class="home-hero__stack">
                <div class="home-hero__card home-hero__card--primary">
                    <?php if ($heroPrimary !== ''): ?>
                        <img src="<?= htmlspecialchars((string)$heroPrimary) ?>" alt="Featured jewellery">
                    <?php else: ?>
                        <div class="product-placeholder">Featured collection</div>
                    <?php endif; ?>
                </div>

                <div class="home-hero__card home-hero__card--secondary">
                    <?php if ($heroSecondary !== ''): ?>
                        <img src="<?= htmlspecialchars((string)$heroSecondary) ?>" alt="Jewellery detail">
                    <?php else: ?>
                        <div class="product-placeholder">Detail</div>
                    <?php endif; ?>
                </div>

                <div class="home-hero__card home-hero__card--tertiary">
                    <?php if ($heroTertiary !== ''): ?>
                        <img src="<?= htmlspecialchars((string)$heroTertiary) ?>" alt="Jewellery styling">
                    <?php else: ?>
                        <div class="product-placeholder">Style</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <?php if ($categories): ?>
        <section class="home-categories">
            <div class="home-categories__header">
                <div>
                    <p class="eyebrow">Shop by category</p>
                    <h2 class="section-heading">Find your next favourite piece.</h2>
                </div>
            </div>

            <div class="home-categories__grid">
                <?php foreach ($categories as $category): ?>
                    <a class="home-categories__item" href="<?= URLROOT ?>/categories/<?= htmlspecialchars((string)$category['slug']) ?>">
                        <span class="home-categories__name"><?= htmlspecialchars((string)$category['name']) ?></span>
                        <span class="home-categories__meta"><?= (int)$category['product_count'] ?> products</span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="home-products">
        <div class="home-products__header">
            <div>
                <p class="eyebrow">Fresh arrivals</p>
                <h2 class="section-heading">New collection</h2>
            </div>
            <a class="home-products__link" href="<?= URLROOT ?>/products">View all products</a>
        </div>

        <?php if (!$products): ?>
            <p>No products available yet.</p>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <article class="product-card home-product-card">
                        <a href="<?= URLROOT ?>/products/<?= htmlspecialchars((string)$product['slug']) ?>" class="link-reset">
                            <div class="product-image-wrap">
                                <?php if (!empty($product['primary_image'])): ?>
                                    <img
                                        src="<?= htmlspecialchars((string)$product['primary_image']) ?>"
                                        alt="<?= htmlspecialchars((string)$product['name']) ?>"
                                        class="product-image"
                                        loading="lazy"
                                    >
                                <?php else: ?>
                                    <div class="product-placeholder">No image</div>
                                <?php endif; ?>
                            </div>

                            <p class="home-product-card__meta">Gold plated jewellery</p>
                        </a>

                        <p class="product-price">
                            <?= htmlspecialchars((string)($product['currency'] ?? 'GBP')) ?>
                            <?= number_format(((int)$product['price_minor']) / 100, 2) ?>
                        </p>

                        <form method="post" action="<?= URLROOT ?>/cart/items">
                            <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="add-cart-btn">Add to cart</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="home-testimonials">
        <div class="home-testimonials__header">
            <div>
                <p class="eyebrow">Customer love</p>
                <h2 class="section-heading">Let customers speak for us</h2>
            </div>
        </div>

        <div class="home-testimonials__grid">
            <?php foreach ($testimonials as $testimonial): ?>
                <article class="home-testimonial">
                    <div class="home-testimonial__rating">★★★★★</div>
                    <p class="home-testimonial__quote"><?= htmlspecialchars((string)$testimonial['quote']) ?></p>
                    <div class="home-testimonial__author"><?= htmlspecialchars((string)$testimonial['author']) ?></div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="home-ambassador">
        <div class="home-ambassador__visual">
            <?php if ($ambassadorVisual !== ''): ?>
                <img src="<?= htmlspecialchars((string)$ambassadorVisual) ?>" alt="Ambassador spotlight" class="product-image">
            <?php endif; ?>
        </div>

        <div class="home-ambassador__content">
            <p class="eyebrow">Ambassador spotlight</p>
            <h2 class="section-heading">Brand story around our jewellery.</h2>
            <p class="section-copy"> Immerse yourself in the story behind our jewellery, told through the eyes of our brand ambassador. A warm, personal connection that brings our collection to life and invites you to be part of the story.

            </p>                
            </p>

            <div class="home-ambassador__actions">
                <a class="btn" href="<?= URLROOT ?>/about">Read our story</a>
                <a class="btn secondary" href="<?= URLROOT ?>/products">Keep shopping</a>
            </div>
        </div>
    </section>
</div>

<?php if (!$products): ?>
    <p>No products available yet.</p>
<?php else: ?>
    <div class="product-grid">
        <?php foreach ($products as $product): ?>
            <div class="product-card">

                <a href="<?= URLROOT ?>/products/<?= htmlspecialchars((string)$product['slug']) ?>" class="link-reset">
                    <div class="product-image-wrap">
                        <?php if (!empty($product['primary_image'])): ?>
                            <img
                                src="<?= htmlspecialchars((string)$product['primary_image']) ?>"
                                alt="<?= htmlspecialchars((string)$product['name']) ?>"
                                class="product-image"
                            >
                        <?php else: ?>
                            <div class="product-placeholder">No image</div>
                        <?php endif; ?>
                    </div>

                </a>
                <p class="product-price">
                    <?= htmlspecialchars((string)($product['currency'] ?? 'GBP')) ?>
                    <?= number_format(((int)$product['price_minor']) / 100, 2) ?>
                </p>

                <form method="post" action="<?= URLROOT ?>/cart/items">
                    <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="add-cart-btn">Add to cart</button>
                </form>

            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
