<?php

declare(strict_types=1);

namespace App\Billing\Infrastructure\Web\Controller;

use App\Billing\Application\Command\CreateInvoiceCommand;
use App\Billing\Application\InvoiceService;
use App\CRM\Application\PersonService;
use App\Billing\Application\QuoteService;
use App\Billing\Domain\Service\InvoiceNumberGenerator;
use App\Billing\Domain\Service\QuoteNumberGenerator;
use App\Billing\Infrastructure\Persistence\PDOInvoiceRepository;
use App\CRM\Infrastructure\Persistence\PDOPersonRepository;
use App\Billing\Infrastructure\Persistence\PDOQuoteRepository;
use App\Core\Infrastructure\Web\Controller\AbstractController;
use App\Core\Infrastructure\Web\Routing\Attribute\{Get, Post};
use App\Core\Infrastructure\Web\Routing\Router;

class InvoiceController extends AbstractController
{
    private InvoiceService $invoiceService;
    private QuoteService $quoteService;
    private PersonService $personService;

    public function __construct(Router $router)
    {
        parent::__construct($router);

        $invoiceRepository = new PDOInvoiceRepository();
        $quoteRepository = new PDOQuoteRepository();
        $personRepository = new PDOPersonRepository();
        $invoiceNumberGenerator = new InvoiceNumberGenerator();
        $quoteNumberGenerator = new QuoteNumberGenerator();
        
        $this->invoiceService = new InvoiceService($invoiceRepository, $quoteRepository, $invoiceNumberGenerator);
        $this->quoteService = new QuoteService($quoteRepository, $quoteNumberGenerator);
        $this->personService = new PersonService($personRepository);
    }

    #[Get('/invoices', 'invoices.index')]
    public function index(): void
    {
        $invoices = $this->invoiceService->findAll();
        
        $this->render('billing/invoice/index', [
            'title' => 'Invoices',
            'invoices' => $invoices
        ]);
    }

    #[Get('/invoices/create', 'invoices.create')]
    public function create(): void
    {
        $quotes = $this->quoteService->findAll();
        
        // Filter only accepted quotes
        $acceptedQuotes = array_filter($quotes, function($quote) {
            return $quote->status()->value === 'accepted';
        });
        
        $this->render('billing/invoice/create', [
            'title' => 'Create Invoice',
            'quotes' => $acceptedQuotes
        ]);
    }

    #[Post('/invoices', 'invoices.store')]
    public function store(): void
    {
        try {
            $quoteId = $this->post('quote_id');
            $dueDays = (int)($this->post('due_days', 30));

            if (!$quoteId) {
                throw new \InvalidArgumentException('Quote is required');
            }

            $issueDate = new \DateTimeImmutable();
            $dueDate = $issueDate->modify("+{$dueDays} days");

            $command = new CreateInvoiceCommand($quoteId, $issueDate, $dueDate);
            $invoice = $this->invoiceService->createInvoice($command);

            $url = $this->urlGenerator()->route('invoices.view', ['id' => $invoice->id()->value()]);
            $this->redirect($url);
        } catch (\Exception $e) {
            $url = $this->urlGenerator()->route('invoices.create', [], ['error' => $e->getMessage()]);
            $this->redirect($url);
        }
    }

    #[Get('/invoices/{id}', 'invoices.view')]
    public function view(?string $id = null): void
    {
        // Support both /invoices/{id} and /invoices/view?id=xxx
        $id = $id ?? $_GET['id'] ?? null;
        
        if (!$id) {
            $url = $this->urlGenerator()->route('invoices.index', [], ['error' => 'missing_id']);
            $this->redirect($url);
            return;
        }

        $invoice = $this->invoiceService->findById($id);
        
        if (!$invoice) {
            $url = $this->urlGenerator()->route('invoices.index', [], ['error' => 'not_found']);
            $this->redirect($url);
            return;
        }

        // Load client info
        $client = $this->personService->findById($invoice->clientId()->value());

        $this->render('billing/invoice/view', [
            'title' => 'Invoice Details',
            'invoice' => $invoice,
            'client' => $client
        ]);
    }

    #[Post('/invoices/{id}/issue', 'invoices.issue')]
    public function issue(?string $id = null): void
    {
        try {
            if (!$id) {
                throw new \InvalidArgumentException('Invoice ID is required');
            }

            $this->invoiceService->issueInvoice($id);
            $url = $this->urlGenerator()->route('invoices.view', ['id' => $id], ['success' => 'issued']);
            $this->redirect($url);
        } catch (\Exception $e) {
            $invoiceId = $id ?? '';
            $url = $this->urlGenerator()->route('invoices.view', ['id' => $invoiceId], ['error' => $e->getMessage()]);
            $this->redirect($url);
        }
    }

    #[Post('/invoices/{id}/pay', 'invoices.pay')]
    public function markAsPaid(?string $id = null): void
    {
        try {
            if (!$id) {
                throw new \InvalidArgumentException('Invoice ID is required');
            }

            $this->invoiceService->markInvoiceAsPaid($id);
            $url = $this->urlGenerator()->route('invoices.view', ['id' => $id], ['success' => 'paid']);
            $this->redirect($url);
        } catch (\Exception $e) {
            $invoiceId = $id ?? '';
            $url = $this->urlGenerator()->route('invoices.view', ['id' => $invoiceId], ['error' => $e->getMessage()]);
            $this->redirect($url);
        }
    }

    #[Post('/invoices/{id}/cancel', 'invoices.cancel')]
    public function cancel(?string $id = null): void
    {
        try {
            if (!$id) {
                throw new \InvalidArgumentException('Invoice ID is required');
            }

            $this->invoiceService->cancelInvoice($id);
            $url = $this->urlGenerator()->route('invoices.view', ['id' => $id], ['success' => 'cancelled']);
            $this->redirect($url);
        } catch (\Exception $e) {
            $invoiceId = $id ?? '';
            $url = $this->urlGenerator()->route('invoices.view', ['id' => $invoiceId], ['error' => $e->getMessage()]);
            $this->redirect($url);
        }
    }
}
