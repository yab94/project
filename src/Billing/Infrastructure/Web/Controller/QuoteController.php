<?php

declare(strict_types=1);

namespace App\Billing\Infrastructure\Web\Controller;

use App\Billing\Application\Command\AddQuoteLineCommand;
use App\Billing\Application\Command\CreateQuoteCommand;
use App\CRM\Application\PersonService;
use App\Billing\Application\QuoteService;
use App\Billing\Domain\Service\QuoteNumberGenerator;
use App\CRM\Infrastructure\Persistence\PDOPersonRepository;
use App\Billing\Infrastructure\Persistence\PDOQuoteRepository;
use App\Core\Infrastructure\Web\Controller\AbstractController;
use App\Core\Infrastructure\Web\Attribute\{Get, Post};
use App\Core\Infrastructure\Web\Router;

class QuoteController extends AbstractController
{
    private QuoteService $quoteService;
    private PersonService $personService;

    public function __construct(Router $router)
    {
        parent::__construct($router);

        $quoteRepository = new PDOQuoteRepository();
        $personRepository = new PDOPersonRepository();
        $numberGenerator = new QuoteNumberGenerator();
        
        $this->quoteService = new QuoteService($quoteRepository, $numberGenerator);
        $this->personService = new PersonService($personRepository);
    }

    #[Get('/quotes', 'quotes.index')]
    public function index(): void
    {
        $quotes = $this->quoteService->findAll();
        
        $this->render('billing/quote/index', [
            'title' => 'Quotes',
            'quotes' => $quotes
        ]);
    }

    #[Get('/quotes/create', 'quotes.create')]
    public function create(): void
    {
        $persons = $this->personService->findAll();
        
        $this->render('billing/quote/create', [
            'title' => 'Create Quote',
            'persons' => $persons
        ]);
    }

    #[Post('/quotes', 'quotes.store')]
    public function store(): void
    {
        try {
            $personId = $this->post('person_id');
            $expiryDays = (int)($this->post('expiry_days', 30));

            if (!$personId) {
                throw new \InvalidArgumentException('Person is required');
            }

            $issueDate = new \DateTimeImmutable();
            $expiryDate = $issueDate->modify("+{$expiryDays} days");

            $command = new CreateQuoteCommand($personId, $issueDate, $expiryDate);
            $quote = $this->quoteService->createQuote($command);

            $url = $this->urlGenerator()->route('quotes.view', ['id' => $quote->id()->value()]);
            $this->redirect($url);
        } catch (\Exception $e) {
            $url = $this->urlGenerator()->route('quotes.create', [], ['error' => $e->getMessage()]);
            $this->redirect($url);
        }
    }

    #[Get('/quotes/{id}', 'quotes.view')]
    public function view(?string $id = null): void
    {
        // Support both /quotes/{id} and /quotes/view?id=xxx
        $id = $id ?? $_GET['id'] ?? null;
        
        if (!$id) {
            $url = $this->urlGenerator()->route('quotes.index', [], ['error' => 'missing_id']);
            $this->redirect($url);
            return;
        }

        $quote = $this->quoteService->findById($id);
        
        if (!$quote) {
            $url = $this->urlGenerator()->route('quotes.index', [], ['error' => 'not_found']);
            $this->redirect($url);
            return;
        }

        // Load client info
        $client = $this->personService->findById($quote->clientId()->value());

        $this->render('billing/quote/view', [
            'title' => 'Quote Details',
            'quote' => $quote,
            'client' => $client
        ]);
    }

    #[Post('/quotes/{id}/lines', 'quotes.addLine')]
    public function addLine(?string $id = null): void
    {
        try {
            $quoteId = $id;
            $description = $this->post('description', '');
            $quantity = (int)($this->post('quantity', 1));
            $unitPrice = (float)($this->post('unit_price', 0));

            if (!$quoteId) {
                throw new \InvalidArgumentException('Quote ID is required');
            }

            $command = new AddQuoteLineCommand($quoteId, $description, $quantity, $unitPrice);
            $this->quoteService->addLine($command);

            $url = $this->urlGenerator()->route('quotes.view', ['id' => $quoteId], ['success' => 'line_added']);
            $this->redirect($url);
        } catch (\Exception $e) {
            $quoteId = $id ?? '';
            $url = $this->urlGenerator()->route('quotes.view', ['id' => $quoteId], ['error' => $e->getMessage()]);
            $this->redirect($url);
        }
    }

    #[Post('/quotes/{id}/send', 'quotes.send')]
    public function send(?string $id = null): void
    {
        try {
            if (!$id) {
                throw new \InvalidArgumentException('Quote ID is required');
            }

            $this->quoteService->sendQuote($id);
            $url = $this->urlGenerator()->route('quotes.view', ['id' => $id], ['success' => 'sent']);
            $this->redirect($url);
        } catch (\Exception $e) {
            $quoteId = $id ?? '';
            $url = $this->urlGenerator()->route('quotes.view', ['id' => $quoteId], ['error' => $e->getMessage()]);
            $this->redirect($url);
        }
    }

    #[Post('/quotes/{id}/accept', 'quotes.accept')]
    public function accept(?string $id = null): void
    {
        try {
            if (!$id) {
                throw new \InvalidArgumentException('Quote ID is required');
            }

            $this->quoteService->acceptQuote($id);
            $url = $this->urlGenerator()->route('quotes.view', ['id' => $id], ['success' => 'accepted']);
            $this->redirect($url);
        } catch (\Exception $e) {
            $quoteId = $id ?? '';
            $url = $this->urlGenerator()->route('quotes.view', ['id' => $quoteId], ['error' => $e->getMessage()]);
            $this->redirect($url);
        }
    }

    #[Post('/quotes/{id}/reject', 'quotes.reject')]
    public function reject(?string $id = null): void
    {
        try {
            if (!$id) {
                throw new \InvalidArgumentException('Quote ID is required');
            }

            $this->quoteService->rejectQuote($id);
            $url = $this->urlGenerator()->route('quotes.view', ['id' => $id], ['success' => 'rejected']);
            $this->redirect($url);
        } catch (\Exception $e) {
            $quoteId = $id ?? '';
            $url = $this->urlGenerator()->route('quotes.view', ['id' => $quoteId], ['error' => $e->getMessage()]);
            $this->redirect($url);
        }
    }
}
