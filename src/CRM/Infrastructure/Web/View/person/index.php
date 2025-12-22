<h2>Persons</h2>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        <?php
        $messages = [
            'created' => 'Person created successfully!',
            'deleted' => 'Person deleted successfully!'
        ];
        echo $messages[$_GET['success']] ?? 'Operation successful!';
        ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error">
        <?php
        $messages = [
            'not_found' => 'Person not found.',
            'missing_id' => 'Person ID is missing.'
        ];
        echo $messages[$_GET['error']] ?? htmlspecialchars($_GET['error']);
        ?>
    </div>
<?php endif; ?>

<div style="margin-bottom: 1.5rem;">
    <a href="<?= $url->route('persons.create') ?>" class="btn btn-success">➕ Create New Person</a>
</div>

<?php if (empty($persons)): ?>
    <div class="alert alert-info">
        No persons found. <a href="<?= $url->route('persons.create') ?>">Create your first person</a>.
    </div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($persons as $person): ?>
                <tr>
                    <td>
                        <span class="badge <?= $person->type()->value === 'individual' ? 'badge-draft' : 'badge-sent' ?>">
                            <?= ucfirst($person->type()->value) ?>
                        </span>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($person->fullName()) ?></strong>
                    </td>
                    <td>
                        <?php
                        $contacts = $person->contacts();
                        $emails = array_filter($contacts, fn($c) => $c->email() !== null);
                        echo !empty($emails) ? htmlspecialchars($emails[array_key_first($emails)]->email()->value()) : '-';
                        ?>
                    </td>
                    <td>
                        <?php
                        $phones = array_filter($contacts, fn($c) => $c->phone() !== null);
                        echo !empty($phones) ? htmlspecialchars($phones[array_key_first($phones)]->phone()->value()) : '-';
                        ?>
                    </td>
                    <td>
                        <a href="<?= $url->route('persons.view', ['id' => $person->id()->value()]) ?>" class="btn btn-sm">View</a>
                        <form method="POST" action="<?= $url->route('persons.delete', ['id' => $person->id()->value()]) ?>" style="display: inline;" onsubmit="return confirm('Delete this person?');">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
