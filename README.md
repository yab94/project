# CRM/Quote/Invoice System

A CRM, Quote, and Invoice management system with DDD (Domain-Driven Design) architecture.

## 🏗️ Architecture

The project follows DDD principles with a clear separation into 3 layers:

```
src/
├── Domain/              # Business layer (Entities, Value Objects, Repositories)
│   ├── CRM/            # Person, Address, Contact management
│   ├── Billing/        # Quote and Invoice management
│   └── Banking/        # Bank account and transaction management
├── Application/         # Application layer (Use Cases, Services, Commands)
│   ├── Service/        # Application services
│   └── Command/        # Command DTOs
└── Infrastructure/      # Infrastructure layer (Persistence, Web)
    ├── Persistence/    # PDO Repositories
    └── Web/            # Controllers and Views (to be implemented)
```

## 🚀 Installation and Setup

### Prerequisites
- Docker and Docker Compose
- Make (optional but recommended)

### Complete Project Initialization

```bash
# Build and start containers
make init

# Or manually:
docker-compose build
docker-compose up -d
docker-compose exec php composer install
```

The application will be accessible at:
- **Application**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081

### Available Make Commands

```bash
make help           # Display help
make start          # Start containers
make stop           # Stop containers
make restart        # Restart containers
make build          # Rebuild images
make install        # Install Composer dependencies
make test           # Run unit tests
make logs           # Display logs
make shell          # Open shell in PHP container
make db-shell       # Open MySQL shell
make db-reset       # Reset database
```

## 🧪 Tests

```bash
# Run unit tests
make test

# Or directly
docker-compose exec php vendor/bin/phpunit

# Tests with coverage
make test-coverage
```

## 📦 Domains

### CRM Domain
- **Person** (individual / company)
- Multiple **Addresses** per person
- **Contacts** (email, phone, etc.)

### Billing Domain
- **Quotes** linked to clients with complete workflow
  - Statuses: draft → sent → accepted/rejected/expired
- **Invoices** linked to clients and quotes (optional)
  - Statuses: draft → issued → paid/cancelled
- **Lines** for quotes and invoices

### Banking Domain
- **Bank Accounts** with IBAN validation
- **Bank Transactions** (debit/credit)
- **Linking** to invoices and payments
- **Reconciliation** management

## 🗄️ Database

The MySQL database is automatically initialized at startup with the complete schema.

**Default Credentials:**
- Host: `mysql` (or `localhost:3306` from host)
- Database: `crm_db`
- User: `crm_user`
- Password: `crm_password`
- Root Password: `root_password`

**Tables (all in English):**
- `persons` - Person entities
- `addresses` - Address entities
- `contacts` - Contact information
- `quotes` - Quotes
- `quote_lines` - Quote lines
- `invoices` - Invoices
- `invoice_lines` - Invoice lines
- `bank_accounts` - Bank accounts
- `bank_transactions` - Bank transactions

## 🌐 Web Interface

The application provides a simple SSR (Server-Side Rendering) web interface without JavaScript framework:

**Routes (in English):**
- `/persons` - Person management
- `/quotes` - Quote management
- `/invoices` - Invoice management
- `/bank-accounts` - Bank account management

## 🔧 Technologies

- **PHP 8.2** with strict typing
- **MySQL 8.0**
- **PHPUnit 10** for tests
- **Docker & Docker Compose** for dev environment
- **Apache** as web server
- **PSR-4** for autoloading

## 📝 Applied Principles

- ✅ Domain-Driven Design (DDD)
- ✅ Layered architecture (Domain, Application, Infrastructure)
- ✅ Immutable Value Objects
- ✅ Rich entities with business logic
- ✅ Unit tests without framework
- ✅ Repository pattern
- ✅ SOLID principles
- ✅ **All code in English** (including complex business concepts)

## 🧩 Project Structure

### Domain Layer (43 files)
**CRM:**
- Value Objects: `PersonId`, `PersonType`, `Email`, `Phone`, `AddressId`, `ContactId`
- Entities: `Person`, `Address`, `Contact`
- Repository Interfaces: `PersonRepositoryInterface`

**Billing:**
- Value Objects: `QuoteId`, `InvoiceId`, `Amount`, `QuoteStatus`, `InvoiceStatus`
- Entities: `Quote`, `QuoteLine`, `Invoice`, `InvoiceLine`
- Repository Interfaces: `QuoteRepositoryInterface`, `InvoiceRepositoryInterface`

**Banking:**
- Value Objects: `BankAccountId`, `BankTransactionId`, `IBAN`, `TransactionType`
- Entities: `BankAccount`, `BankTransaction`
- Repository Interfaces: `BankAccountRepositoryInterface`, `BankTransactionRepositoryInterface`

### Application Layer (8 files)
**Commands:**
- `CreatePersonCommand`
- `CreateQuoteCommand`
- `AddQuoteLineCommand`
- `CreateInvoiceCommand`

**Services:**
- `PersonService`
- `QuoteService`
- `InvoiceService`
- `BankAccountService`

### Infrastructure Layer (6 files)
**Persistence:**
- `Database` - PDO connection manager
- `PDOPersonRepository`
- `PDOQuoteRepository`
- `PDOInvoiceRepository`
- `PDOBankAccountRepository`
- `PDOBankTransactionRepository`

### Tests (4 files)
- `PersonTest` - Person entity tests
- `EmailTest` - Email value object tests
- `AmountTest` - Amount value object tests
- `QuoteTest` - Quote entity and workflow tests

## 🎯 Key Features

### Quote Workflow
1. **Create** quote in draft status
2. **Add lines** (description, quantity, unit price)
3. **Send** quote to client
4. Client can **accept** or **reject**
5. Quote can **expire** automatically

### Invoice Workflow
1. **Create** invoice from accepted quote
2. **Issue** invoice (changes status from draft to issued)
3. **Mark as paid** (with amount validation)
4. Can be **cancelled** if not paid

### Bank Account Management
1. **Create** bank account with IBAN
2. **Add transactions** (credit/debit)
3. **Link** transactions to invoices
4. **Reconcile** transactions

## 📚 Translation Reference

French business terms translated to English:
- Personne (morale/physique) → Person (company/individual)
- Devis → Quote
- Facture → Invoice
- Montant → Amount
- Brouillon → Draft
- Envoyé → Sent
- Accepté → Accepted
- Refusé → Rejected
- Expiré → Expired
- Émise → Issued
- Payée → Paid
- Annulée → Cancelled
- Compte bancaire → Bank account
- Opération bancaire → Bank transaction

## 🛠️ Development

To add new features:

1. **Start with Domain**: Create entities and value objects
2. **Add Application logic**: Create services and commands
3. **Implement Infrastructure**: Create repositories
4. **Write Tests**: Add unit tests for new features
5. **Update Database**: Modify `docker/init.sql` if needed

## 📄 License

This project is for educational and demonstration purposes.
