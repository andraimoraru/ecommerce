<?php
$statusCode = (int)($data['status_code'] ?? 404);
$errorTitle = (string)($data['error_title'] ?? 'Page not found');
$errorMessage = (string)($data['error_message'] ?? 'We could not find what you were looking for.');
$actions = $data['actions'] ?? [];
?>

<section class="error-page" aria-labelledby="errorPageTitle">
    <p class="error-page__code"><?= $statusCode ?></p>
    <h1 id="errorPageTitle" class="error-page__title"><?= htmlspecialchars($errorTitle) ?></h1>
    <p class="error-page__message"><?= htmlspecialchars($errorMessage) ?></p>

    <?php if ($actions): ?>
        <div class="error-page__actions">
            <?php foreach ($actions as $action): ?>
                <a
                    href="<?= htmlspecialchars((string)($action['url'] ?? URLROOT)) ?>"
                    class="<?= htmlspecialchars((string)($action['class'] ?? 'btn')) ?>"
                >
                    <?= htmlspecialchars((string)($action['label'] ?? 'Continue')) ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
