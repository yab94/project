<h2>Quotes</h2>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        Operation successful!
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error">
        <?php
        $messages = [
            'not_found' => 'Quote not found.',
            'missing_id' => 'Quote ID is missing.'
        ];
        echo $messages[$_GET['error']] ?? htmlspecialchars($_GET['error']);
        ?>
    </div>
<?php endif; ?>

<div style="margin-bottom: 1.5rem;">
    <a href="<?= $url->route('quotes.create') ?>" class="btn btn-success">➕ Create New Quote</a>
</div>

<?php if (empty($quotes)): ?>
    <div class="alert alert-info">
        No quotes found. <a href="<?= $url->route('quotes.create') ?>">Create your first quote</a>.
    </div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Number</th>
                <th>Client</th>
                <th>Created</th>
                <th>Valid Until</th>
                <th>Status</th>
                <th>Total</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($quotes as $quote): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($quote->number()) ?></strong></td>
                    <td>ID: <?= htmlspecialchars($quote->clientId()->value()) ?></td>
                    <td><?= $quote->createdAt()->format('Y-m-d') ?></td>
                    <td><?= $quote->validUntil()->format('Y-m-d') ?></td>
                    <td>
                        <span class="badge badge-<?= $quote->status()->value ?>">
                            <?= ucfirst($quote->status()->value) ?>
                        </span>
                    </td>
                    <td><strong><?= number_format($quote->totalAmount()->value(), 2) ?> EUR</strong></td>
                    <td>
                        <a href="<?= $url->route('quotes.view', [], ['id' => $quote->id()->value()]) ?>" class="btn btn-sm">View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
