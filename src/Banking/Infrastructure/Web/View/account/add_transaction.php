<div class="container">
    <h2>Add Transaction - <?= htmlspecialchars($account->accountName()) ?></h2>

    <div class="account-summary">
        <p><strong>Current Balance:</strong> <?= number_format($account->balance(), 2) ?> €</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= $url('bank_accounts.store_transaction', ['id' => (string) $account->id()]) ?>">
        <div class="form-group">
            <label for="type">Transaction Type:</label>
            <select id="type" name="type" required>
                <option value="deposit" <?= ($old['type'] ?? '') === 'deposit' ? 'selected' : '' ?>>Deposit (+)</option>
                <option value="withdrawal" <?= ($old['type'] ?? '') === 'withdrawal' ? 'selected' : '' ?>>Withdrawal (-)</option>
            </select>
        </div>

        <div class="form-group">
            <label for="amount">Amount (€):</label>
            <input 
                type="number" 
                id="amount" 
                name="amount" 
                value="<?= htmlspecialchars($old['amount'] ?? '') ?>"
                step="0.01"
                min="0.01"
                required
            >
        </div>

        <div class="form-group">
            <label for="description">Description:</label>
            <input 
                type="text" 
                id="description" 
                name="description" 
                value="<?= htmlspecialchars($old['description'] ?? '') ?>"
                placeholder="e.g., Salary, Rent payment, etc."
                required
            >
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Add Transaction</button>
            <a href="<?= $url('bank_accounts.show', ['id' => (string) $account->id()]) ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<style>
    .account-summary {
        background: #e9ecef;
        padding: 1rem;
        border-radius: 4px;
        margin-bottom: 1.5rem;
    }
</style>
