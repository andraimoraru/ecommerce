<h1><?= htmlspecialchars((string)($data['title'] ?? 'Marketing')) ?></h1>

<p class="admin-meta">
    Manage the social profile settings for future marketing integrations.
</p>

<?php $channels = $data['channels'] ?? []; ?>
<?php $settings = $data['settings'] ?? []; ?>
<?php $errors = $data['errors'] ?? []; ?>

<form method="post" action="<?= URLROOT ?>/admin/marketing/social">
    <div class="grid-2">
        <?php foreach ($channels as $channel): ?>
            <?php $row = $settings[$channel] ?? []; ?>
            <?php $channelErrors = $errors[$channel] ?? []; ?>

            <fieldset>
                <legend><?= htmlspecialchars(ucfirst(strtolower((string)$channel))) ?></legend>

                <input
                    type="hidden"
                    name="settings[<?= htmlspecialchars((string)$channel) ?>][channel]"
                    value="<?= htmlspecialchars((string)$channel) ?>"
                >

                <?php if (!empty($channelErrors['channel'])): ?>
                    <p class="text-danger"><?= htmlspecialchars((string)$channelErrors['channel']) ?></p>
                <?php endif; ?>

                <div class="stack-sm">
                    <label>Profile URL</label><br>
                    <input
                        type="url"
                        name="settings[<?= htmlspecialchars((string)$channel) ?>][profile_url]"
                        value="<?= htmlspecialchars((string)($row['profile_url'] ?? '')) ?>"
                        placeholder="https://..."
                    >
                    <?php if (!empty($channelErrors['profile_url'])): ?>
                        <p class="text-danger"><?= htmlspecialchars((string)$channelErrors['profile_url']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="stack-sm">
                    <label>Username</label><br>
                    <input
                        type="text"
                        name="settings[<?= htmlspecialchars((string)$channel) ?>][username]"
                        value="<?= htmlspecialchars((string)($row['username'] ?? '')) ?>"
                        maxlength="120"
                    >
                    <?php if (!empty($channelErrors['username'])): ?>
                        <p class="text-danger"><?= htmlspecialchars((string)$channelErrors['username']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="stack-sm">
                    <label>Page ID</label><br>
                    <input
                        type="text"
                        name="settings[<?= htmlspecialchars((string)$channel) ?>][page_id]"
                        value="<?= htmlspecialchars((string)($row['page_id'] ?? '')) ?>"
                        maxlength="120"
                    >
                    <?php if (!empty($channelErrors['page_id'])): ?>
                        <p class="text-danger"><?= htmlspecialchars((string)$channelErrors['page_id']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="stack-sm">
                    <label>Access token</label><br>
                    <input
                        type="password"
                        name="settings[<?= htmlspecialchars((string)$channel) ?>][access_token]"
                        value=""
                        autocomplete="new-password"
                        placeholder="<?= ($row['access_token_status'] ?? '') === 'saved' ? 'Token saved - leave blank to keep it' : 'Paste access token' ?>"
                    >
                    <p class="admin-meta">
                        Token status: <?= (($row['access_token_status'] ?? '') === 'saved') ? 'saved' : 'not saved' ?>
                    </p>
                </div>

                <?php if (!empty($row['updated_at'])): ?>
                    <p class="admin-meta">Last updated: <?= htmlspecialchars((string)$row['updated_at']) ?></p>
                <?php endif; ?>
            </fieldset>
        <?php endforeach; ?>
    </div>

    <div class="admin-button-row">
        <button class="btn" type="submit">Save Marketing Settings</button>
        <a class="btn secondary" href="<?= URLROOT ?>/admin">Back to Admin</a>
    </div>
</form>
