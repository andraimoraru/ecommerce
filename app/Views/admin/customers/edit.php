<?php $customer = $data['customer'] ?? null; ?>
<?php $errors = $data['errors'] ?? []; ?>

<?php if (!$customer): ?>
    <p>Customer not found.</p>
<?php else: ?>
    <p class="admin-back-row">
        <a href="<?= URLROOT ?>/admin/customers/<?= (int)$customer['id'] ?>">← Back to customer</a>
    </p>

    <h1 class="admin-title-reset"><?= htmlspecialchars((string)($data['title'] ?? 'Edit Customer')) ?></h1>

    <div class="card admin-form-card">
        <form method="post" action="<?= URLROOT ?>/admin/customers/<?= (int)$customer['id'] ?>">
            <div class="checkout-grid">
                <div>
                    <label>First Name</label><br>
                    <input name="first_name" value="<?= htmlspecialchars((string)$customer['first_name']) ?>">
                    <?php if (!empty($errors['first_name'])): ?><p class="text-danger"><?= htmlspecialchars((string)$errors['first_name']) ?></p><?php endif; ?>
                </div>

                <div>
                    <label>Last Name</label><br>
                    <input name="last_name" value="<?= htmlspecialchars((string)$customer['last_name']) ?>">
                    <?php if (!empty($errors['last_name'])): ?><p class="text-danger"><?= htmlspecialchars((string)$errors['last_name']) ?></p><?php endif; ?>
                </div>

                <div>
                    <label>Email</label><br>
                    <input name="email" value="<?= htmlspecialchars((string)$customer['email']) ?>">
                    <?php if (!empty($errors['email'])): ?><p class="text-danger"><?= htmlspecialchars((string)$errors['email']) ?></p><?php endif; ?>
                </div>

                <div>
                    <label>Phone</label><br>
                    <input name="phone" value="<?= htmlspecialchars((string)($customer['phone'] ?? '')) ?>">
                </div>

                <div class="admin-field--full">
                    <label>
                        <input type="checkbox" name="is_active" value="1" <?= ((int)($customer['is_active'] ?? 0) === 1) ? 'checked' : '' ?>>
                        Active customer
                    </label>
                </div>
            </div>

            <div class="admin-form-actions">
                <button class="btn" type="submit">Save Customer</button>
                <a class="btn secondary" href="<?= URLROOT ?>/admin/customers/<?= (int)$customer['id'] ?>">Cancel</a>
            </div>
        </form>
    </div>
<?php endif; ?>
