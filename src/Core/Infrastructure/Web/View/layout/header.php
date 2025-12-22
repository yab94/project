<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'CRM') ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; line-height: 1.6; color: #333; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        header { background: #2c3e50; color: white; padding: 1rem 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        header h1 { font-size: 1.5rem; }
        nav { margin-top: 1rem; }
        nav a { color: white; text-decoration: none; padding: 0.5rem 1rem; margin-right: 0.5rem; background: rgba(255,255,255,0.1); border-radius: 4px; display: inline-block; transition: background 0.3s; }
        nav a:hover { background: rgba(255,255,255,0.2); }
        nav a.active { background: #3498db; }
        main { background: white; margin: 2rem auto; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); min-height: 500px; }
        h2 { color: #2c3e50; margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 2px solid #3498db; }
        .btn { display: inline-block; padding: 0.6rem 1.2rem; background: #3498db; color: white; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; font-size: 1rem; transition: background 0.3s; }
        .btn:hover { background: #2980b9; }
        .btn-success { background: #27ae60; }
        .btn-success:hover { background: #229954; }
        .btn-danger { background: #e74c3c; }
        .btn-danger:hover { background: #c0392b; }
        .btn-warning { background: #f39c12; }
        .btn-warning:hover { background: #d68910; }
        .btn-secondary { background: #95a5a6; }
        .btn-secondary:hover { background: #7f8c8d; }
        .btn-sm { padding: 0.4rem 0.8rem; font-size: 0.9rem; }
        .alert { padding: 1rem; margin-bottom: 1.5rem; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        table th, table td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #ddd; }
        table th { background: #f8f9fa; font-weight: 600; color: #2c3e50; }
        table tr:hover { background: #f8f9fa; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2c3e50; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.6rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #3498db; }
        .badge { display: inline-block; padding: 0.25rem 0.6rem; border-radius: 12px; font-size: 0.85rem; font-weight: 600; }
        .badge-draft { background: #95a5a6; color: white; }
        .badge-sent { background: #3498db; color: white; }
        .badge-accepted { background: #27ae60; color: white; }
        .badge-rejected { background: #e74c3c; color: white; }
        .badge-expired { background: #7f8c8d; color: white; }
        .badge-issued { background: #f39c12; color: white; }
        .badge-paid { background: #27ae60; color: white; }
        .badge-cancelled { background: #e74c3c; color: white; }
        .card { background: #f8f9fa; padding: 1.5rem; border-radius: 4px; margin-bottom: 1rem; }
        .card-header { font-weight: 600; color: #2c3e50; margin-bottom: 1rem; font-size: 1.1rem; }
        .actions { margin-top: 1.5rem; }
        .actions .btn { margin-right: 0.5rem; margin-bottom: 0.5rem; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>🏢 CRM / Quotes / Invoices System</h1>
            <nav>
                <a href="<?= $url->route('home') ?>">Home</a>
                <a href="<?= $url->route('persons.index') ?>">Persons</a>
                <a href="<?= $url->route('quotes.index') ?>">Quotes</a>
                <a href="<?= $url->route('invoices.index') ?>">Invoices</a>
                <a href="/bank-accounts">Bank Accounts</a>
            </nav>
        </div>
    </header>
    <main class="container">
