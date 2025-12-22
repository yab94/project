<h2>Quote #<?= htmlspecialchars($quote->number()) ?></h2>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        <?php
        $messages = [
            'line_added' => 'Line added successfully!',
            'sent' => 'Quote sent to client!',
            'accepted' => 'Quote accepted!',
            'rejected' => 'Quote rejected.'
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
        <div class="card-header">Quote Information</div>
        <table style="width: 100%;">
            <tr>
                <td style="font-weight: 600; padding: 0.5rem 0;">Number:</td>
                <td style="padding: 0.5rem 0;"><?= htmlspecialchars($quote->number()) ?></td>
            </tr>
            <tr>
                <td style="font-weight: 600; padding: 0.5rem 0;">Client:</td>
                <td style="padding: 0.5rem 0;">
                    <?php if ($client): ?>
                        <a href="<?= $url->route('persons.view', ['id' => $client->id()->value()]) ?>"><?= htmlspecialchars($client->fullName()) ?></a>
                    <?php else: ?>
                        ID: <?= htmlspecialchars($quote->clientId()->value()) ?>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td style="font-weight: 600; padding: 0.5rem 0;">Created:</td>
                <td style="padding: 0.5rem 0;"><?= $quote->createdAt()->format('F d, Y') ?></td>
            </tr>
            <tr>
                <td style="font-weight: 600; padding: 0.5rem 0;">Valid Until:</td>
                <td style="padding: 0.5rem 0;"><?= $quote->validUntil()->format('F d, Y') ?></td>
            </tr>
            <tr>
                <td style="font-weight: 600; padding: 0.5rem 0;">Status:</td>
                <td style="padding: 0.5rem 0;">
                    <span class="badge badge-<?= $quote->status()->value ?>">
                        <?= ucfirst($quote->status()->value) ?>
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <div class="card">
        <div class="card-header">Total Amount</div>
        <div style="font-size: 2rem; font-weight: bold; color: #27ae60; text-align: center; padding: 1rem 0;">
            <?= number_format($quote->totalAmount()->value(), 2) ?> EUR
        </div>
        <div style="text-align: center; color: #7f8c8d; font-size: 0.9rem;">
            <?= count($quote->lines()) ?> line(s)
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Quote Lines</div>
    
    <?php if (empty($quote->lines())): ?>
        <p style="color: #7f8c8d;">No lines added yet.</p>
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
                <?php foreach ($quote->lines() as $index => $line): ?>
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
                    <td style="text-align: right; padding: 1rem; font-size: 1.2rem; color: #27ae60;">
                        <?= number_format($quote->totalAmount()->value(), 2) ?> EUR
                    </td>
                </tr>
            </tfoot>
        </table>
    <?php endif; ?>

    <?php if ($quote->status()->value === 'draft'): ?>
        <div style="margin-top: 1.5rem; padding: 1.5rem; background: #f8f9fa; border-radius: 4px;">
            <h3 style="color: #2c3e50; margin-bottom: 1rem;">➕ Add New Line</h3>
            <form method="POST" action="<?= $url->route('quotes.addLine', ['id' => $quote->id()->value()]) ?>">
                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 1rem; align-items: end;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="description">Description *</label>
                        <input type="text" name="description" id="description" required placeholder="Service or product description">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="quantity">Quantity *</label>
                        <input type="number" name="quantity" id="quantity" value="1" required min="1">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="unit_price">Unit Price *</label>
                        <input type="number" name="unit_price" id="unit_price" step="0.01" required min="0" placeholder="0.00">
                    </div>
                    <button type="submit" class="btn btn-success">Add Line</button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<div class="actions">
    <a href="<?= $url->route('quotes.index') ?>" class="btn btn-secondary">Back to List</a>
    
    <?php if ($quote->status()->value === 'draft'): ?>
        <form method="POST" action="<?= $url->route('quotes.send', ['id' => $quote->id()->value()]) ?>" style="display: inline;" onsubmit="return confirm('Send this quote to the client?');">
            <button type="submit" class="btn" <?= empty($quote->lines()) ? 'disabled' : '' ?>>📧 Send Quote</button>
        </form>
    <?php endif; ?>
    
    <?php if ($quote->status()->value === 'sent'): ?>
        <form method="POST" action="<?= $url->route('quotes.accept', ['id' => $quote->id()->value()]) ?>" style="display: inline;">
            <button type="submit" class="btn btn-success">✅ Accept</button>
        </form>
        <form method="POST" action="<?= $url->route('quotes.reject', ['id' => $quote->id()->value()]) ?>" style="display: inline;">
            <button type="submit" class="btn btn-danger">❌ Reject</button>
        </form>
    <?php endif; ?>
    
    <?php if ($quote->status()->value === 'accepted'): ?>
        <a href="<?= $url->route('invoices.create', [], ['quote_id' => $quote->id()->value()]) ?>" class="btn btn-success">🧾 Create Invoice</a>
    <?php endif; ?>
</div>
