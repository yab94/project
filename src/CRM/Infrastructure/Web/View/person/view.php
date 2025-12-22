<h2>Person Details</h2>

<div class="card">
    <div class="card-header">
        <span class="badge <?= $person->type()->value === 'individual' ? 'badge-draft' : 'badge-sent' ?>">
            <?= ucfirst($person->type()->value) ?>
        </span>
        <?= htmlspecialchars($person->fullName()) ?>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
        <div>
            <h3 style="color: #2c3e50; margin-bottom: 1rem;">Basic Information</h3>
            <table style="width: 100%;">
                <tr>
                    <td style="font-weight: 600; padding: 0.5rem 0;">ID:</td>
                    <td style="padding: 0.5rem 0;"><?= htmlspecialchars($person->id()->value()) ?></td>
                </tr>
                <tr>
                    <td style="font-weight: 600; padding: 0.5rem 0;">Type:</td>
                    <td style="padding: 0.5rem 0;"><?= ucfirst($person->type()->value) ?></td>
                </tr>
                <?php if ($person->type()->value === 'individual'): ?>
                    <tr>
                        <td style="font-weight: 600; padding: 0.5rem 0;">First Name:</td>
                        <td style="padding: 0.5rem 0;"><?= htmlspecialchars($person->firstName() ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; padding: 0.5rem 0;">Last Name:</td>
                        <td style="padding: 0.5rem 0;"><?= htmlspecialchars($person->name()) ?></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td style="font-weight: 600; padding: 0.5rem 0;">Company Name:</td>
                        <td style="padding: 0.5rem 0;"><?= htmlspecialchars($person->companyName() ?? '-') ?></td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>

        <div>
            <h3 style="color: #2c3e50; margin-bottom: 1rem;">📍 Addresses</h3>
            <?php if (empty($person->addresses())): ?>
                <p style="color: #7f8c8d;">No addresses registered.</p>
            <?php else: ?>
                <?php foreach ($person->addresses() as $address): ?>
                    <div style="padding: 0.75rem; background: white; border-left: 3px solid #3498db; margin-bottom: 0.5rem;">
                        <?= htmlspecialchars($address->street()) ?><br>
                        <?= htmlspecialchars($address->postalCode()) ?> <?= htmlspecialchars($address->city()) ?><br>
                        <strong><?= htmlspecialchars($address->country()) ?></strong>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div style="margin-top: 1.5rem;">
        <h3 style="color: #2c3e50; margin-bottom: 1rem;">📞 Contacts</h3>
        <?php if (empty($person->contacts())): ?>
            <p style="color: #7f8c8d;">No contact information.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Email</th>
                        <th>Phone</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($person->contacts() as $contact): ?>
                        <tr>
                            <td><?= htmlspecialchars($contact->type() ?? 'Main') ?></td>
                            <td><?= $contact->email() ? htmlspecialchars($contact->email()->value()) : '-' ?></td>
                            <td><?= $contact->phone() ? htmlspecialchars($contact->phone()->value()) : '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<div class="actions">
    <a href="<?= $url->route('persons.index') ?>" class="btn btn-secondary">Back to List</a>
    <a href="<?= $url->route('quotes.create', [], ['person_id' => $person->id()->value()]) ?>" class="btn btn-success">Create Quote</a>
</div>
