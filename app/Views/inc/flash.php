<?php
$flash = $_SESSION['flash'] ?? null;

if (!is_array($flash) || empty($flash['message'])) {
    return;
}

$flashType = (string)($flash['type'] ?? 'info');
$flashMessage = (string)$flash['message'];
unset($_SESSION['flash']);
?>

<div class="flash flash--<?= htmlspecialchars($flashType) ?>" role="status" aria-live="polite">
    <div class="flash__body">
        <strong class="flash__title">
            <?php if ($flashType === 'success'): ?>
                Done
            <?php elseif ($flashType === 'error'): ?>
                Please check this
            <?php else: ?>
                Update
            <?php endif; ?>
        </strong>
        <span class="flash__message"><?= htmlspecialchars($flashMessage) ?></span>
    </div>
</div>
