<h2>Invoices</h2>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        Operation successful!
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error">
        <?php
        $messages = [
            'not_found' => 'Invoice not found.',
            'missing_id' => 'Invoice ID is missing.'
        ];
        echo $messages[$_GET['error']] ?? htmlspecialchars($_GET['error']);
        ?>
    </div>
<?php endif; ?>

<div style="margin-bottom: 1.5rem;">
    <a href="<?= $url->route('invoices.create') ?>" class="btn btn-success">➕ Create New Invoice</a>
</div>

<?php if (empty($invoices)): ?>
    <div class="alert alert-info">
        No invoices found. <a href="<?= $url->route('invoices.create') ?>">Create your first invoice</a>.
    </div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Number</th>
                <th>Client</th>
                <th>Issued</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>Total</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($invoices as $invoice): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($invoice->number()) ?></strong></td>
                    <td>ID: <?= htmlspecialchars($invoice->clientId()->value()) ?></td>
                    <td><?= $invoice->issuedAt()->format('Y-m-d') ?></td>
                    <td><?= $invoice->dueDate()->format('Y-m-d') ?></td>
                    <td>
                        <span class="badge badge-<?= $invoice->status()->value ?>">
                            <?= ucfirst($invoice->status()->value) ?>
                        </span>
                    </td>
                    <td><strong><?= number_format($invoice->totalAmount()->value(), 2) ?> EUR</strong></td>
                    <td>
                        <a href="<?= $url->route('invoices.view', [], ['id' => $invoice->id()->value()]) ?>" class="btn btn-sm">View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
