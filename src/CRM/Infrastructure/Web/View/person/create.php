<h2>Create Person</h2>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error">
        <?= htmlspecialchars($_GET['error']) ?>
    </div>
<?php endif; ?>

<form method="POST" action="<?= $url->route('persons.store') ?>">
    <div class="form-group">
        <label for="type">Type *</label>
        <select name="type" id="type" required onchange="toggleFields()">
            <option value="individual">Individual (Person)</option>
            <option value="company">Company</option>
        </select>
    </div>

    <div class="form-group" id="name-field">
        <label for="name">Name *</label>
        <input type="text" name="name" id="name" required placeholder="Enter full name or company name">
        <small style="color: #7f8c8d;">For individuals: "John Doe" will be split automatically</small>
    </div>

    <div class="form-group" id="siret-field" style="display: none;">
        <label for="siret">SIRET</label>
        <input type="text" name="siret" id="siret" placeholder="Company registration number">
    </div>

    <div class="actions">
        <button type="submit" class="btn btn-success">Create Person</button>
        <a href="<?= $url->route('persons.index') ?>" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<script>
function toggleFields() {
    const type = document.getElementById('type').value;
    const siretField = document.getElementById('siret-field');
    const nameLabel = document.querySelector('label[for="name"]');
    const namePlaceholder = document.getElementById('name');
    
    if (type === 'company') {
        siretField.style.display = 'block';
        nameLabel.textContent = 'Company Name *';
        namePlaceholder.placeholder = 'Enter company name';
    } else {
        siretField.style.display = 'none';
        nameLabel.textContent = 'Name *';
        namePlaceholder.placeholder = 'Enter full name (e.g., John Doe)';
    }
}
</script>
