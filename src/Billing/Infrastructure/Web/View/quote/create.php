<h2>Create Quote</h2>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error">
        <?= htmlspecialchars($_GET['error']) ?>
    </div>
<?php endif; ?>

<form method="POST" action="<?= $url->route('quotes.store') ?>">
    <div class="form-group">
        <label for="person_id">Client *</label>
        <select name="person_id" id="person_id" required>
            <option value="">-- Select a client --</option>
            <?php foreach ($persons as $person): ?>
                <option value="<?= $person->id()->value() ?>" <?= (isset($_GET['person_id']) && $_GET['person_id'] === $person->id()->value()) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($person->fullName()) ?> (<?= ucfirst($person->type()->value) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="expiry_days">Valid for (days) *</label>
        <input type="number" name="expiry_days" id="expiry_days" value="30" required min="1" max="365">
        <small style="color: #7f8c8d;">Number of days this quote will be valid</small>
    </div>

    <div class="alert alert-info">
        ℹ️ The quote will be created in <strong>Draft</strong> status. You can add lines and send it to the client later.
    </div>

    <div class="actions">
        <button type="submit" class="btn btn-success">Create Quote</button>
        <a href="<?= $url->route('quotes.index') ?>" class="btn btn-secondary">Cancel</a>
    </div>
</form>
