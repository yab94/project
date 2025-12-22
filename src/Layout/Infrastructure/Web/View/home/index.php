<h2>Welcome to CRM System</h2>

<div style="margin: 2rem 0;">
    <p style="font-size: 1.1rem; color: #555; margin-bottom: 2rem;">
        Manage your customers, quotes, invoices, and bank accounts with this DDD-based system.
    </p>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 2rem;">
    <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <div class="card-header" style="color: white; font-size: 1.3rem;">👥 Persons</div>
        <p style="margin-bottom: 1rem;">Manage individuals and companies</p>
        <a href="<?= $url->route('persons.index') ?>" class="btn" style="background: white; color: #667eea;">View Persons</a>
    </div>

    <div class="card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
        <div class="card-header" style="color: white; font-size: 1.3rem;">📋 Quotes</div>
        <p style="margin-bottom: 1rem;">Create and manage quotes</p>
        <a href="<?= $url->route('quotes.index') ?>" class="btn" style="background: white; color: #f5576c;">View Quotes</a>
    </div>

    <div class="card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
        <div class="card-header" style="color: white; font-size: 1.3rem;">🧾 Invoices</div>
        <p style="margin-bottom: 1rem;">Issue and track invoices</p>
        <a href="<?= $url->route('invoices.index') ?>" class="btn" style="background: white; color: #4facfe;">View Invoices</a>
    </div>

    <div class="card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white;">
        <div class="card-header" style="color: white; font-size: 1.3rem;">🏦 Bank Accounts</div>
        <p style="margin-bottom: 1rem;">Manage accounts & transactions</p>
        <a href="/bank-accounts" class="btn" style="background: white; color: #43e97b;">View Accounts</a>
    </div>
</div>

<div style="margin-top: 3rem; padding: 1.5rem; background: #f8f9fa; border-left: 4px solid #3498db; border-radius: 4px;">
    <h3 style="color: #2c3e50; margin-bottom: 1rem;">🎯 System Features</h3>
    <ul style="list-style: none; padding: 0;">
        <li style="padding: 0.5rem 0;">✅ <strong>Domain-Driven Design</strong> architecture</li>
        <li style="padding: 0.5rem 0;">✅ <strong>PHP 8.2</strong> with strict typing</li>
        <li style="padding: 0.5rem 0;">✅ <strong>Complete business logic</strong> in entities</li>
        <li style="padding: 0.5rem 0;">✅ <strong>Repository pattern</strong> for data persistence</li>
        <li style="padding: 0.5rem 0;">✅ <strong>Full workflow</strong> for quotes and invoices</li>
    </ul>
</div>
