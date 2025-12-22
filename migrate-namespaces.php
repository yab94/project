#!/usr/bin/env php
<?php

/**
 * Script to migrate namespaces from horizontal layers to vertical slices
 * 
 * Transformations:
 * - App\Domain\CRM\Entity -> App\Modules\CRM\Domain\Entity
 * - App\Domain\Billing\Entity -> App\Modules\Billing\Domain\Entity
 * - App\Domain\Banking\Entity -> App\Modules\Banking\Domain\Entity
 * - App\Domain\Shared -> App\Modules\Shared\Domain
 * - App\Application\Service -> App\Modules\{Module}\Application
 * - App\Application\Command -> App\Modules\Shared\Application\Command
 * - App\Infrastructure\Web\Controller\CRM -> App\Modules\CRM\Infrastructure\Web\Controller
 * - App\Infrastructure\Persistence\PDO -> App\Modules\{Module}\Infrastructure\Persistence
 */

$rootDir = __DIR__;
$modulesDir = $rootDir . '/src/Modules';

// Mapping rules for namespace transformations
$namespaceRules = [
    // Domain layer
    'namespace App\\Domain\\CRM\\' => 'namespace App\\Modules\\CRM\\Domain\\',
    'namespace App\\Domain\\Billing\\' => 'namespace App\\Modules\\Billing\\Domain\\',
    'namespace App\\Domain\\Banking\\' => 'namespace App\\Modules\\Banking\\Domain\\',
    'namespace App\\Domain\\Shared' => 'namespace App\\Modules\\Shared\\Domain',
    
    // Application layer
    'namespace App\\Application\\Service' => 'namespace App\\Modules\\CRM\\Application', // Will need manual check
    'namespace App\\Application\\Command' => 'namespace App\\Modules\\Shared\\Application\\Command',
    
    // Infrastructure layer
    'namespace App\\Infrastructure\\Web\\Controller\\CRM' => 'namespace App\\Modules\\CRM\\Infrastructure\\Web\\Controller',
    'namespace App\\Infrastructure\\Web\\Controller\\Billing' => 'namespace App\\Modules\\Billing\\Infrastructure\\Web\\Controller',
    'namespace App\\Infrastructure\\Web\\Controller\\Banking' => 'namespace App\\Modules\\Banking\\Infrastructure\\Web\\Controller',
    'namespace App\\Infrastructure\\Persistence' => 'namespace App\\Modules\\{Module}\\Infrastructure\\Persistence',
];

// Import/use statement rules
$useRules = [
    // Domain entities
    'use App\\Domain\\CRM\\' => 'use App\\Modules\\CRM\\Domain\\',
    'use App\\Domain\\Billing\\' => 'use App\\Modules\\Billing\\Domain\\',
    'use App\\Domain\\Banking\\' => 'use App\\Modules\\Banking\\Domain\\',
    'use App\\Domain\\Shared\\' => 'use App\\Modules\\Shared\\Domain\\',
    
    // Application
    'use App\\Application\\Service\\PersonService' => 'use App\\Modules\\CRM\\Application\\PersonService',
    'use App\\Application\\Service\\QuoteService' => 'use App\\Modules\\Billing\\Application\\QuoteService',
    'use App\\Application\\Service\\InvoiceService' => 'use App\\Modules\\Billing\\Application\\InvoiceService',
    'use App\\Application\\Service\\BankAccountService' => 'use App\\Modules\\Banking\\Application\\BankAccountService',
    'use App\\Application\\Command\\' => 'use App\\Modules\\Shared\\Application\\Command\\',
    
    // Infrastructure
    'use App\\Infrastructure\\Persistence\\PDOPersonRepository' => 'use App\\Modules\\CRM\\Infrastructure\\Persistence\\PDOPersonRepository',
    'use App\\Infrastructure\\Persistence\\PDOQuoteRepository' => 'use App\\Modules\\Billing\\Infrastructure\\Persistence\\PDOQuoteRepository',
    'use App\\Infrastructure\\Persistence\\PDOInvoiceRepository' => 'use App\\Modules\\Billing\\Infrastructure\\Persistence\\PDOInvoiceRepository',
    'use App\\Infrastructure\\Persistence\\PDOBankAccountRepository' => 'use App\\Modules\\Banking\\Infrastructure\\Persistence\\PDOBankAccountRepository',
    'use App\\Infrastructure\\Persistence\\PDOBankTransactionRepository' => 'use App\\Modules\\Banking\\Infrastructure\\Persistence\\PDOBankTransactionRepository',
];

function processFile(string $filePath, array $namespaceRules, array $useRules): bool
{
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Detect module from file path
    $module = null;
    if (preg_match('#/Modules/(CRM|Billing|Banking|Shared)/#', $filePath, $matches)) {
        $module = $matches[1];
    }
    
    // Replace namespace declarations
    foreach ($namespaceRules as $old => $new) {
        if ($new === 'namespace App\\Modules\\{Module}\\Infrastructure\\Persistence' && $module) {
            $new = "namespace App\\Modules\\{$module}\\Infrastructure\\Persistence";
        }
        $content = str_replace($old, $new, $content);
    }
    
    // Replace use statements
    foreach ($useRules as $old => $new) {
        $content = str_replace($old, $new, $content);
    }
    
    // Check if any changes were made
    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        return true;
    }
    
    return false;
}

// Find all PHP files in Modules
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($modulesDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

$updatedFiles = [];
$unchangedFiles = [];

foreach ($iterator as $file) {
    if ($file->getExtension() === 'php') {
        $filePath = $file->getPathname();
        if (processFile($filePath, $namespaceRules, $useRules)) {
            $updatedFiles[] = $filePath;
        } else {
            $unchangedFiles[] = $filePath;
        }
    }
}

echo "✅ Migration complete!\n\n";
echo "Updated files: " . count($updatedFiles) . "\n";
echo "Unchanged files: " . count($unchangedFiles) . "\n\n";

if (!empty($updatedFiles)) {
    echo "Updated:\n";
    foreach ($updatedFiles as $file) {
        echo "  - " . str_replace($rootDir . '/', '', $file) . "\n";
    }
}

echo "\n⚠️  Manual verification needed for:\n";
echo "  - src/Infrastructure/Web/routes.php (controller references)\n";
echo "  - src/Infrastructure/Web/AbstractController.php (view path resolution)\n";
echo "  - Any remaining old src/ directories\n";
