<?php

declare(strict_types=1);

namespace App\Billing\Application;

use App\Billing\Application\Command\CreateInvoiceCommand;
use App\Billing\Domain\AggregateRoot\Invoice;
use App\Billing\Domain\ValueObject\InvoiceLine;
use App\Billing\Domain\Repository\InvoiceRepositoryInterface;
use App\Billing\Domain\Repository\QuoteRepositoryInterface;
use App\Billing\Domain\Service\InvoiceNumberGenerator;
use App\Billing\Domain\ValueObject\InvoiceId;
use App\Billing\Domain\ValueObject\QuoteId;
use App\Billing\Domain\ValueObject\QuoteStatus;
use App\CRM\Domain\ValueObject\PersonId;

final class InvoiceService
{
    private InvoiceRepositoryInterface $invoiceRepository;
    private QuoteRepositoryInterface $quoteRepository;
    private InvoiceNumberGenerator $numberGenerator;

    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository,
        QuoteRepositoryInterface $quoteRepository,
        InvoiceNumberGenerator $numberGenerator
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->quoteRepository = $quoteRepository;
        $this->numberGenerator = $numberGenerator;
    }

    public function createInvoice(CreateInvoiceCommand $command): Invoice
    {
        $quote = $this->quoteRepository->findById(new QuoteId($command->quoteId));
        if (!$quote) {
            throw new \DomainException("Quote not found: {$command->quoteId}");
        }

        if ($quote->status() !== QuoteStatus::ACCEPTED) {
            throw new \DomainException('Only accepted quotes can be invoiced');
        }

        $number = $this->numberGenerator->generate($command->issueDate);

        $invoice = Invoice::create(
            $quote->clientId(),
            $number,
            $command->dueDate,
            new QuoteId($command->quoteId)
        );

        foreach ($quote->lines() as $quoteLine) {
            $invoice->addLine(
                new InvoiceLine(
                    $quoteLine->description(),
                    $quoteLine->quantity(),
                    $quoteLine->unitPrice()
                )
            );
        }

        $this->invoiceRepository->save($invoice);

        return $invoice;
    }

    public function issueInvoice(string $invoiceId): void
    {
        $invoice = $this->invoiceRepository->findById(new InvoiceId($invoiceId));
        if (!$invoice) {
            throw new \DomainException("Invoice not found: {$invoiceId}");
        }

        $invoice->issue();
        $this->invoiceRepository->save($invoice);
    }

    public function markInvoiceAsPaid(string $invoiceId): void
    {
        $invoice = $this->invoiceRepository->findById(new InvoiceId($invoiceId));
        if (!$invoice) {
            throw new \DomainException("Invoice not found: {$invoiceId}");
        }

        $invoice->markAsPaid($invoice->totalAmount());
        $this->invoiceRepository->save($invoice);
    }

    public function cancelInvoice(string $invoiceId): void
    {
        $invoice = $this->invoiceRepository->findById(new InvoiceId($invoiceId));
        if (!$invoice) {
            throw new \DomainException("Invoice not found: {$invoiceId}");
        }

        $invoice->cancel();
        $this->invoiceRepository->save($invoice);
    }

    public function findById(string $id): ?Invoice
    {
        return $this->invoiceRepository->findById(new InvoiceId($id));
    }

    /** @return Invoice[] */
    public function findAll(): array
    {
        return $this->invoiceRepository->findAll();
    }

    /** @return Invoice[] */
    public function findByClientId(string $clientId): array
    {
        return $this->invoiceRepository->findByClientId(new PersonId($clientId));
    }

    public function deleteInvoice(string $id): void
    {
        $this->invoiceRepository->delete(new InvoiceId($id));
    }
}
