<h2>Error</h2>

<div class="alert alert-error">
    <strong>Oops! Something went wrong.</strong>
    <?php if (isset($message)): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
</div>

<div class="actions">
    <a href="<?= $url->route('home') ?>" class="btn">Go to Home</a>
    <a href="javascript:history.back()" class="btn btn-secondary">Go Back</a>
</div>
