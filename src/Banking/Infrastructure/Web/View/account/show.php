<div class="container">
    <div class="header-actions">
        <h2><?= htmlspecialchars($account->name()) ?></h2>
        <div>
            <a href="<?= $url->route('bank_accounts.add_transaction', ['id' => $account->id()->value()]) ?>" class="btn btn-primary">
                Add Transaction
            </a>
            <a href="<?= $url->route('bank_accounts.index') ?>" class="btn btn-secondary">Back to List</a>
        </div>
    </div>

    <div class="account-info">
        <div class="info-grid">
            <div>
                <strong>IBAN:</strong>
                <p><?= htmlspecialchars($account->iban()->formatted()) ?></p>
            </div>
            <div>
                <strong>Balance:</strong>
                <p class="balance <?= $account->balance()->value() < 0 ? 'negative' : 'positive' ?>">
                    <?= number_format($account->balance()->value(), 2) ?> €
                </p>
            </div>
            <div>
                <strong>Status:</strong>
                <p>
                    <span class="badge badge-success">
                        Active
                    </span>
                </p>
            </div>
        </div>
    </div>

    <h3>Transactions</h3>

    <?php if (empty($transactions)): ?>
        <p>No transactions yet.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Balance After</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?= $transaction->transactionDate()->format('Y-m-d H:i') ?></td>
                        <td>
                            <span class="badge <?= $transaction->type()->isDeposit() ? 'badge-success' : 'badge-warning' ?>">
                                <?= $transaction->type()->isDeposit() ? 'Deposit' : 'Withdrawal' ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($transaction->description()) ?></td>
                        <td class="<?= $transaction->type()->isDeposit() ? 'positive' : 'negative' ?>">
                            <?= $transaction->type()->isDeposit() ? '+' : '-' ?>
                            <?= number_format($transaction->amount(), 2) ?> €
                        </td>
                        <td><?= number_format($transaction->balanceAfter(), 2) ?> €</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <form method="POST" action="<?= $url->route('bank_accounts.destroy', ['id' => $account->id()->value()]) ?>" 
          onsubmit="return confirm('Are you sure you want to delete this account?');" 
          style="margin-top: 2rem;">
        <input type="hidden" name="_method" value="DELETE">
        <button type="submit" class="btn btn-danger">Delete Account</button>
    </form>
</div>

<style>
    .account-info {
        background: #f5f5f5;
        padding: 1.5rem;
        border-radius: 8px;
        margin-bottom: 2rem;
    }
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
    }
    .balance {
        font-size: 1.5rem;
        font-weight: bold;
    }
    .positive {
        color: #28a745;
    }
    .negative {
        color: #dc3545;
    }
</style>
