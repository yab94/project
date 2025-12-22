<div class="container">
    <div class="header-actions">
        <h2>Bank Accounts</h2>
        <a href="<?= $url('bank_accounts.create') ?>" class="btn btn-primary">Create New Account</a>
    </div>

    <?php if (empty($accounts)): ?>
        <p>No bank accounts found. <a href="<?= $url('bank_accounts.create') ?>">Create one now</a>.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Account Name</th>
                    <th>IBAN</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($accounts as $account): ?>
                    <tr>
                        <td><?= htmlspecialchars($account->accountName()) ?></td>
                        <td><?= htmlspecialchars((string) $account->iban()) ?></td>
                        <td><?= number_format($account->balance(), 2) ?> €</td>
                        <td>
                            <span class="badge <?= $account->isActive() ? 'badge-success' : 'badge-danger' ?>">
                                <?= $account->isActive() ? 'Active' : 'Closed' ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= $url('bank_accounts.show', ['id' => (string) $account->id()]) ?>">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
