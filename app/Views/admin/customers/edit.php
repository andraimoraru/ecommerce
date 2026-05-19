<?php $customer = $data['customer'] ?? null; ?>
<?php $errors = $data['errors'] ?? []; ?>

<?php if (!$customer): ?>
    <p>Customer not found.</p>
<?php else: ?>
    <p style="margin:0 0 18px 0;">
        <a href="<?= URLROOT ?>/admin/customers/<?= (int)$customer['id'] ?>">← Back to customer</a>
    </p>

    <h1 style="margin-top:0;"><?= htmlspecialchars((string)($data['title'] ?? 'Edit Customer')) ?></h1>

    <div class="card" style="max-width:780px;">
        <form method="post" action="<?= URLROOT ?>/admin/customers/<?= (int)$customer['id'] ?>">
            <div class="checkout-grid">
                <div>
                    <label>First Name</label><br>
                    <input name="first_name" value="<?= htmlspecialchars((string)$customer['first_name']) ?>" style="width:100%;">
                    <?php if (!empty($errors['first_name'])): ?><p style="color:red;"><?= htmlspecialchars((string)$errors['first_name']) ?></p><?php endif; ?>
                </div>

                <div>
                    <label>Last Name</label><br>
                    <input name="last_name" value="<?= htmlspecialchars((string)$customer['last_name']) ?>" style="width:100%;">
                    <?php if (!empty($errors['last_name'])): ?><p style="color:red;"><?= htmlspecialchars((string)$errors['last_name']) ?></p><?php endif; ?>
                </div>

                <div>
                    <label>Email</label><br>
                    <input name="email" value="<?= htmlspecialchars((string)$customer['email']) ?>" style="width:100%;">
                    <?php if (!empty($errors['email'])): ?><p style="color:red;"><?= htmlspecialchars((string)$errors['email']) ?></p><?php endif; ?>
                </div>

                <div>
                    <label>Phone</label><br>
                    <input name="phone" value="<?= htmlspecialchars((string)($customer['phone'] ?? '')) ?>" style="width:100%;">
                </div>

                <div style="grid-column:1 / -1;">
                    <label>
                        <input type="checkbox" name="is_active" value="1" <?= ((int)($customer['is_active'] ?? 0) === 1) ? 'checked' : '' ?>>
                        Active customer
                    </label>
                </div>
            </div>

            <div style="display:flex; gap:12px; margin-top:16px;">
                <button class="btn" type="submit">Save Customer</button>
                <a class="btn secondary" href="<?= URLROOT ?>/admin/customers/<?= (int)$customer['id'] ?>">Cancel</a>
            </div>
        </form>
    </div>
<?php endif; ?>
