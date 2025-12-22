<h2>Invoice #<?= htmlspecialchars($invoice->number()) ?></h2>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        <?php
        $messages = [
            'issued' => 'Invoice issued successfully!',
            'paid' => 'Invoice marked as paid!',
            'cancelled' => 'Invoice cancelled.'
        ];
        echo $messages[$_GET['success']] ?? 'Operation successful!';
        ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error">
        <?= htmlspecialchars($_GET['error']) ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
    <div class="card">
        <div class="card-header">Invoice Information</div>
        <table style="width: 100%;">
            <tr>
                <td style="font-weight: 600; padding: 0.5rem 0;">Number:</td>
                <td style="padding: 0.5rem 0;"><?= htmlspecialchars($invoice->number()) ?></td>
            </tr>
            <tr>
                <td style="font-weight: 600; padding: 0.5rem 0;">Client:</td>
                <td style="padding: 0.5rem 0;">
                    <?php if ($client): ?>
                        <a href="<?= $url->route('persons.view', ['id' => $client->id()->value()]) ?>"><?= htmlspecialchars($client->fullName()) ?></a>
                    <?php else: ?>
                        ID: <?= htmlspecialchars($invoice->clientId()->value()) ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php if ($invoice->quoteId()): ?>
            <tr>
                <td style="font-weight: 600; padding: 0.5rem 0;">From Quote:</td>
                <td style="padding: 0.5rem 0;">
                    <a href="<?= $url->route('quotes.view', [], ['id' => $invoice->quoteId()->value()]) ?>">View Quote</a>
                </td>
            </tr>
            <?php endif; ?>
            <tr>
                <td style="font-weight: 600; padding: 0.5rem 0;">Issued:</td>
                <td style="padding: 0.5rem 0;"><?= $invoice->issuedAt()->format('F d, Y') ?></td>
            </tr>
            <tr>
                <td style="font-weight: 600; padding: 0.5rem 0;">Due Date:</td>
                <td style="padding: 0.5rem 0;">
                    <?= $invoice->dueDate()->format('F d, Y') ?>
                    <?php
                    $daysUntilDue = (new \DateTime())->diff($invoice->dueDate())->days;
                    $isOverdue = $invoice->dueDate() < new \DateTimeImmutable();
                    if ($invoice->status()->value === 'issued'):
                        if ($isOverdue):
                    ?>
                        <span style="color: #e74c3c; font-weight: bold;">(OVERDUE)</span>
                    <?php else: ?>
                        <span style="color: #f39c12;">(<?= $daysUntilDue ?> days)</span>
                    <?php endif; endif; ?>
                </td>
            </tr>
            <tr>
                <td style="font-weight: 600; padding: 0.5rem 0;">Status:</td>
                <td style="padding: 0.5rem 0;">
                    <span class="badge badge-<?= $invoice->status()->value ?>">
                        <?= ucfirst($invoice->status()->value) ?>
                    </span>
                </td>
            </tr>
            <?php if ($invoice->paidAmount()): ?>
            <tr>
                <td style="font-weight: 600; padding: 0.5rem 0;">Paid Amount:</td>
                <td style="padding: 0.5rem 0; color: #27ae60; font-weight: bold;">
                    <?= number_format($invoice->paidAmount()->value(), 2) ?> EUR
                </td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <div class="card">
        <div class="card-header">Total Amount</div>
        <div style="font-size: 2rem; font-weight: bold; color: <?= $invoice->status()->value === 'paid' ? '#27ae60' : '#3498db' ?>; text-align: center; padding: 1rem 0;">
            <?= number_format($invoice->totalAmount()->value(), 2) ?> EUR
        </div>
        <div style="text-align: center; color: #7f8c8d; font-size: 0.9rem;">
            <?= count($invoice->lines()) ?> line(s)
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Invoice Lines</div>
    
    <?php if (empty($invoice->lines())): ?>
        <p style="color: #7f8c8d;">No lines.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th style="text-align: right;">Quantity</th>
                    <th style="text-align: right;">Unit Price</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoice->lines() as $index => $line): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($line->description()) ?></td>
                        <td style="text-align: right;"><?= $line->quantity() ?></td>
                        <td style="text-align: right;"><?= number_format($line->unitPrice()->value(), 2) ?> EUR</td>
                        <td style="text-align: right;"><strong><?= number_format($line->totalAmount()->value(), 2) ?> EUR</strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background: #f8f9fa; font-weight: bold;">
                    <td colspan="4" style="text-align: right; padding: 1rem;">TOTAL:</td>
                    <td style="text-align: right; padding: 1rem; font-size: 1.2rem; color: <?= $invoice->status()->value === 'paid' ? '#27ae60' : '#3498db' ?>;">
                        <?= number_format($invoice->totalAmount()->value(), 2) ?> EUR
                    </td>
                </tr>
            </tfoot>
        </table>
    <?php endif; ?>
</div>

<div class="actions">
    <a href="<?= $url->route('invoices.index') ?>" class="btn btn-secondary">Back to List</a>
    
    <?php if ($invoice->status()->value === 'draft'): ?>
        <form method="POST" action="<?= $url->route('invoices.issue', ['id' => $invoice->id()->value()]) ?>" style="display: inline;" onsubmit="return confirm('Issue this invoice?');">
            <button type="submit" class="btn btn-warning">📤 Issue Invoice</button>
        </form>
    <?php endif; ?>
    
    <?php if ($invoice->status()->value === 'issued'): ?>
        <form method="POST" action="<?= $url->route('invoices.pay', ['id' => $invoice->id()->value()]) ?>" style="display: inline;" onsubmit="return confirm('Mark this invoice as paid?');">
            <button type="submit" class="btn btn-success">✅ Mark as Paid</button>
        </form>
    <?php endif; ?>
    
    <?php if (in_array($invoice->status()->value, ['draft', 'issued'])): ?>
        <form method="POST" action="<?= $url->route('invoices.cancel', ['id' => $invoice->id()->value()]) ?>" style="display: inline;" onsubmit="return confirm('Cancel this invoice? This cannot be undone.');">
            <button type="submit" class="btn btn-danger">❌ Cancel Invoice</button>
        </form>
    <?php endif; ?>
</div>
