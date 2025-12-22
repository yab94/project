<?php

declare(strict_types=1);

namespace App\Banking\Infrastructure\Web\Controller;

use App\Banking\Application\BankAccountService;
use App\Banking\Domain\Repository\BankAccountRepositoryInterface;
use App\Banking\Domain\Repository\BankTransactionRepositoryInterface;
use App\Banking\Domain\ValueObject\BankAccountId;
use App\Banking\Domain\ValueObject\IBAN;
use App\Banking\Domain\ValueObject\TransactionType;
use App\Banking\Infrastructure\Persistence\PDOBankAccountRepository;
use App\Banking\Infrastructure\Persistence\PDOBankTransactionRepository;
use App\Core\Infrastructure\Persistence\Database;
use App\Core\Infrastructure\Web\Attribute\Delete;
use App\Core\Infrastructure\Web\Attribute\Get;
use App\Core\Infrastructure\Web\Attribute\Post;
use App\Core\Infrastructure\Web\Controller\AbstractController;
use App\Core\Infrastructure\Web\Router;

class BankAccountController extends AbstractController
{
    private BankAccountRepositoryInterface $accountRepository;
    private BankTransactionRepositoryInterface $transactionRepository;
    private BankAccountService $accountService;

    public function __construct(Router $router)
    {
        parent::__construct($router);
        
        $pdo = Database::getConnection();
        $this->accountRepository = new PDOBankAccountRepository($pdo);
        $this->transactionRepository = new PDOBankTransactionRepository($pdo);
        $this->accountService = new BankAccountService(
            $this->accountRepository,
            $this->transactionRepository
        );
    }

    #[Get('/bank-accounts', 'bank_accounts.index')]
    public function index(): void
    {
        $accounts = $this->accountRepository->findAll();

        $this->render('banking/account/index', [
            'title' => 'Bank Accounts',
            'accounts' => $accounts
        ]);
    }

    #[Get('/bank-accounts/create', 'bank_accounts.create')]
    public function create(): void
    {
        $this->render('banking/account/create', [
            'title' => 'Create Bank Account'
        ]);
    }

    #[Post('/bank-accounts', 'bank_accounts.store')]
    public function store(): void
    {
        $accountName = $this->post('account_name');
        $iban = $this->post('iban');
        $bic = $this->post('bic');
        $initialBalance = (float) ($this->post('initial_balance') ?? 0);

        try {
            // Create the account
            $account = $this->accountService->createBankAccount(
                $accountName,
                $iban,
                $bic ?: null
            );

            // If there's an initial balance, add it as a deposit transaction
            if ($initialBalance > 0) {
                $this->accountService->addTransaction(
                    $account->id()->value(),
                    new \DateTimeImmutable(),
                    'credit',
                    $initialBalance,
                    'Initial balance'
                );
            }

            $this->redirect($this->url('bank_accounts.show', ['id' => $account->id()->value()]));
        } catch (\Exception $e) {
            $this->render('banking/account/create', [
                'title' => 'Create Bank Account',
                'error' => $e->getMessage(),
                'old' => $this->post()
            ]);
        }
    }

    #[Get('/bank-accounts/{id}', 'bank_accounts.show')]
    public function show(string $id): void
    {
        $account = $this->accountRepository->findById(new BankAccountId($id));

        if (!$account) {
            $this->notFound();
            return;
        }

        $transactions = $this->transactionRepository->findByBankAccountId($account->id());

        $this->render('banking/account/show', [
            'title' => 'Bank Account - ' . $account->name(),
            'account' => $account,
            'transactions' => $transactions
        ]);
    }

    #[Get('/bank-accounts/{id}/transaction', 'bank_accounts.add_transaction')]
    public function addTransaction(string $id): void
    {
        $account = $this->accountRepository->findById(new BankAccountId($id));

        if (!$account) {
            $this->notFound();
            return;
        }

        $this->render('banking/account/add_transaction', [
            'title' => 'Add Transaction',
            'account' => $account
        ]);
    }

    #[Post('/bank-accounts/{id}/transaction', 'bank_accounts.store_transaction')]
    public function storeTransaction(string $id): void
    {
        $account = $this->accountRepository->findById(new BankAccountId($id));

        if (!$account) {
            $this->notFound();
            return;
        }

        $type = $this->post('type');
        $amount = (float) $this->post('amount');
        $description = $this->post('description');

        try {
            // Map form type to transaction type
            $transactionType = $type === 'deposit' ? 'credit' : 'debit';
            
            $this->accountService->addTransaction(
                $id,
                new \DateTimeImmutable(),
                $transactionType,
                $amount,
                $description
            );

            $this->redirect($this->url('bank_accounts.show', ['id' => $id]));
        } catch (\Exception $e) {
            $this->render('banking/account/add_transaction', [
                'title' => 'Add Transaction',
                'account' => $account,
                'error' => $e->getMessage(),
                'old' => $this->post()
            ]);
        }
    }

    #[Delete('/bank-accounts/{id}', 'bank_accounts.destroy')]
    public function destroy(string $id): void
    {
        try {
            // Delete the account using the repository
            $this->accountRepository->delete(new BankAccountId($id));
            $this->redirect($this->url('bank_accounts.index'));
        } catch (\Exception $e) {
            $this->redirect($this->url('bank_accounts.show', ['id' => $id]));
        }
    }
}
