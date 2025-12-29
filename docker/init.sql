-- Initialize database schema
USE app_db;

-- Table Persons
CREATE TABLE IF NOT EXISTS persons (
    id VARCHAR(50) PRIMARY KEY,
    type ENUM('individual', 'company') NOT NULL,
    name VARCHAR(255) NOT NULL,
    first_name VARCHAR(255),
    company_name VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_persons_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table Addresses
CREATE TABLE IF NOT EXISTS addresses (
    id VARCHAR(50) PRIMARY KEY,
    person_id VARCHAR(50) NOT NULL,
    street VARCHAR(255) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    city VARCHAR(255) NOT NULL,
    country VARCHAR(100) DEFAULT 'France',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table Contacts
CREATE TABLE IF NOT EXISTS contacts (
    id VARCHAR(50) PRIMARY KEY,
    person_id VARCHAR(50) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    type VARCHAR(50) DEFAULT 'main',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table Quotes
CREATE TABLE IF NOT EXISTS quotes (
    id VARCHAR(50) PRIMARY KEY,
    client_id VARCHAR(50) NOT NULL,
    number VARCHAR(50) UNIQUE NOT NULL,
    created_at DATETIME NOT NULL,
    valid_until DATETIME NOT NULL,
    status ENUM('draft', 'sent', 'accepted', 'rejected', 'expired') DEFAULT 'draft',
    notes TEXT,
    FOREIGN KEY (client_id) REFERENCES persons(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table Quote Lines
CREATE TABLE IF NOT EXISTS quote_lines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quote_id VARCHAR(50) NOT NULL,
    line_number INT NOT NULL,
    description VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (quote_id) REFERENCES quotes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table Invoices
CREATE TABLE IF NOT EXISTS invoices (
    id VARCHAR(50) PRIMARY KEY,
    client_id VARCHAR(50) NOT NULL,
    quote_id VARCHAR(50),
    number VARCHAR(50) UNIQUE NOT NULL,
    issued_at DATETIME NOT NULL,
    due_date DATETIME NOT NULL,
    status ENUM('draft', 'issued', 'paid', 'cancelled') DEFAULT 'draft',
    notes TEXT,
    paid_amount DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES persons(id),
    FOREIGN KEY (quote_id) REFERENCES quotes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table Invoice Lines
CREATE TABLE IF NOT EXISTS invoice_lines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id VARCHAR(50) NOT NULL,
    line_number INT NOT NULL,
    description VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table Bank Accounts
CREATE TABLE IF NOT EXISTS bank_accounts (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    iban VARCHAR(50) NOT NULL,
    bic VARCHAR(20),
    balance DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table Bank Transactions
CREATE TABLE IF NOT EXISTS bank_transactions (
    id VARCHAR(50) PRIMARY KEY,
    bank_account_id VARCHAR(50) NOT NULL,
    date DATETIME NOT NULL,
    type ENUM('debit', 'credit') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    label VARCHAR(255) NOT NULL,
    invoice_id VARCHAR(50),
    reconciled BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (bank_account_id) REFERENCES bank_accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Indexes for performance
CREATE INDEX idx_addresses_person ON addresses(person_id);
CREATE INDEX idx_contacts_person ON contacts(person_id);
CREATE INDEX idx_quotes_client ON quotes(client_id);
CREATE INDEX idx_quotes_status ON quotes(status);
CREATE INDEX idx_invoices_client ON invoices(client_id);
CREATE INDEX idx_invoices_quote ON invoices(quote_id);
CREATE INDEX idx_invoices_status ON invoices(status);
CREATE INDEX idx_transactions_account ON bank_transactions(bank_account_id);
CREATE INDEX idx_transactions_invoice ON bank_transactions(invoice_id);
