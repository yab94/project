<?php

declare(strict_types=1);

namespace App\Billing\Application;

use App\Billing\Application\Command\CreateQuoteCommand;
use App\Billing\Application\Command\AddQuoteLineCommand;
use App\Billing\Domain\AggregateRoot\Quote;
use App\Billing\Domain\ValueObject\QuoteLine;
use App\Billing\Domain\Repository\QuoteRepositoryInterface;
use App\Billing\Domain\Service\QuoteNumberGenerator;
use App\Billing\Domain\ValueObject\Amount;
use App\Billing\Domain\ValueObject\QuoteId;
use App\CRM\Domain\ValueObject\PersonId;

final class QuoteService
{
    private QuoteRepositoryInterface $quoteRepository;
    private QuoteNumberGenerator $numberGenerator;

    public function __construct(
        QuoteRepositoryInterface $quoteRepository,
        QuoteNumberGenerator $numberGenerator
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->numberGenerator = $numberGenerator;
    }

    public function createQuote(CreateQuoteCommand $command): Quote
    {
        $number = $this->numberGenerator->generate($command->issueDate);
        
        $quote = Quote::create(
            new PersonId($command->personId),
            $number,
            $command->expiryDate
        );

        $this->quoteRepository->save($quote);

        return $quote;
    }

    public function addLine(AddQuoteLineCommand $command): void
    {
        $quote = $this->quoteRepository->findById(new QuoteId($command->quoteId));
        if (!$quote) {
            throw new \DomainException("Quote not found: {$command->quoteId}");
        }

        $line = new QuoteLine(
            $command->description,
            $command->quantity,
            new Amount($command->unitPrice)
        );

        $quote->addLine($line);
        $this->quoteRepository->save($quote);
    }

    public function removeLine(string $quoteId, int $lineNumber): void
    {
        $quote = $this->quoteRepository->findById(new QuoteId($quoteId));
        if (!$quote) {
            throw new \DomainException("Quote not found: {$quoteId}");
        }

        $quote->removeLine($lineNumber);
        $this->quoteRepository->save($quote);
    }

    public function sendQuote(string $quoteId): void
    {
        $quote = $this->quoteRepository->findById(new QuoteId($quoteId));
        if (!$quote) {
            throw new \DomainException("Quote not found: {$quoteId}");
        }

        $quote->send();
        $this->quoteRepository->save($quote);
    }

    public function acceptQuote(string $quoteId): void
    {
        $quote = $this->quoteRepository->findById(new QuoteId($quoteId));
        if (!$quote) {
            throw new \DomainException("Quote not found: {$quoteId}");
        }

        $quote->accept();
        $this->quoteRepository->save($quote);
    }

    public function rejectQuote(string $quoteId): void
    {
        $quote = $this->quoteRepository->findById(new QuoteId($quoteId));
        if (!$quote) {
            throw new \DomainException("Quote not found: {$quoteId}");
        }

        $quote->reject();
        $this->quoteRepository->save($quote);
    }

    public function findById(string $id): ?Quote
    {
        return $this->quoteRepository->findById(new QuoteId($id));
    }

    /** @return Quote[] */
    public function findAll(): array
    {
        return $this->quoteRepository->findAll();
    }

    /** @return Quote[] */
    public function findByClientId(string $clientId): array
    {
        return $this->quoteRepository->findByClientId(new PersonId($clientId));
    }

    public function deleteQuote(string $id): void
    {
        $this->quoteRepository->delete(new QuoteId($id));
    }
}
