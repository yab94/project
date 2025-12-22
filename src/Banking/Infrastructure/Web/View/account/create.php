<div class="container">
    <h2>Create Bank Account</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= $url->route('bank_accounts.store') ?>">
        <div class="form-group">
            <label for="account_name">Account Name:</label>
            <input 
                type="text" 
                id="account_name" 
                name="account_name" 
                value="<?= htmlspecialchars($old['account_name'] ?? '') ?>"
                required
            >
        </div>

        <div class="form-group">
            <label for="iban">IBAN:</label>
            <input 
                type="text" 
                id="iban" 
                name="iban" 
                value="<?= htmlspecialchars($old['iban'] ?? '') ?>"
                placeholder="FR7630001007941234567890185"
                required
            >
            <small>Format: FR76 3000 1007 9412 3456 7890 185</small>
        </div>

        <div class="form-group">
            <label for="bic">BIC/SWIFT (Optional):</label>
            <input 
                type="text" 
                id="bic" 
                name="bic" 
                value="<?= htmlspecialchars($old['bic'] ?? '') ?>"
                placeholder="BNPAFRPPXXX"
                maxlength="11"
            >
            <small>Format: 8 or 11 characters</small>
        </div>

        <div class="form-group">
            <label for="initial_balance">Initial Balance (€):</label>
            <input 
                type="number" 
                id="initial_balance" 
                name="initial_balance" 
                value="<?= htmlspecialchars($old['initial_balance'] ?? '0') ?>"
                step="0.01"
                min="0"
            >
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create Account</button>
            <a href="<?= $url->route('bank_accounts.index') ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
