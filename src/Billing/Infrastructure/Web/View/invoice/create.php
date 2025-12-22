<h2>Create Invoice</h2>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error">
        <?= htmlspecialchars($_GET['error']) ?>
    </div>
<?php endif; ?>

<?php if (empty($quotes)): ?>
    <div class="alert alert-error">
        No accepted quotes available. You need to <a href="<?= $url->route('quotes.create') ?>">create and accept a quote</a> first.
    </div>
    <div class="actions">
        <a href="<?= $url->route('quotes.index') ?>" class="btn">Go to Quotes</a>
        <a href="<?= $url->route('invoices.index') ?>" class="btn btn-secondary">Back to Invoices</a>
    </div>
<?php else: ?>
    <form method="POST" action="<?= $url->route('invoices.store') ?>">
        <div class="form-group">
            <label for="quote_id">Quote *</label>
            <select name="quote_id" id="quote_id" required onchange="showQuoteDetails(this)">
                <option value="">-- Select an accepted quote --</option>
                <?php foreach ($quotes as $quote): ?>
                    <option value="<?= $quote->id()->value() ?>" 
                            data-number="<?= htmlspecialchars($quote->number()) ?>"
                            data-total="<?= number_format($quote->totalAmount()->value(), 2) ?>"
                            <?= (isset($_GET['quote_id']) && $_GET['quote_id'] === $quote->id()->value()) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($quote->number()) ?> - 
                        <?= number_format($quote->totalAmount()->value(), 2) ?> EUR - 
                        Created: <?= $quote->createdAt()->format('Y-m-d') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="quote-details" style="display: none; margin-bottom: 1.5rem;">
            <div class="card">
                <div class="card-header">Selected Quote Details</div>
                <p><strong>Number:</strong> <span id="detail-number"></span></p>
                <p><strong>Total:</strong> <span id="detail-total"></span> EUR</p>
            </div>
        </div>

        <div class="form-group">
            <label for="due_days">Payment due in (days) *</label>
            <input type="number" name="due_days" id="due_days" value="30" required min="1" max="365">
            <small style="color: #7f8c8d;">Number of days until payment is due</small>
        </div>

        <div class="alert alert-info">
            ℹ️ The invoice will be created in <strong>Draft</strong> status with all lines from the quote. You can issue it later.
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-success">Create Invoice</button>
            <a href="<?= $url->route('invoices.index') ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>

    <script>
    function showQuoteDetails(select) {
        const option = select.options[select.selectedIndex];
        if (option.value) {
            document.getElementById('detail-number').textContent = option.dataset.number;
            document.getElementById('detail-total').textContent = option.dataset.total;
            document.getElementById('quote-details').style.display = 'block';
        } else {
            document.getElementById('quote-details').style.display = 'none';
        }
    }
    // Trigger on page load if quote is pre-selected
    window.addEventListener('load', function() {
        const select = document.getElementById('quote_id');
        if (select.value) {
            showQuoteDetails(select);
        }
    });
    </script>
<?php endif; ?>
