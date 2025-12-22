<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Billing\Domain\AggregateRoot\Quote;
use App\Billing\Domain\ValueObject\QuoteLine;
use App\Billing\Domain\ValueObject\Amount;
use App\Billing\Domain\ValueObject\QuoteStatus;
use App\CRM\Domain\ValueObject\PersonId;

final class QuoteTest extends TestCase
{
    public function testCreateQuote(): void
    {
        $clientId = PersonId::generate();
        $validUntil = new \DateTimeImmutable('+30 days');
        
        $quote = Quote::create($clientId, 'QT-2024-001', $validUntil);

        $this->assertEquals(QuoteStatus::DRAFT, $quote->status());
        $this->assertEquals('QT-2024-001', $quote->number());
        $this->assertEquals($clientId, $quote->clientId());
    }

    public function testAddLineToQuote(): void
    {
        $quote = Quote::create(
            PersonId::generate(),
            'QT-2024-001',
            new \DateTimeImmutable('+30 days')
        );

        $line = new QuoteLine('Service A', 2, new Amount(100));
        $quote->addLine($line);

        $this->assertCount(1, $quote->lines());
    }

    public function testCannotAddLineToNonDraftQuote(): void
    {
        $quote = Quote::create(
            PersonId::generate(),
            'QT-2024-001',
            new \DateTimeImmutable('+30 days')
        );

        // Add a line first so the quote can be sent
        $quote->addLine(new QuoteLine('Service A', 1, new Amount(100)));
        
        // Send the quote (changes status from DRAFT to SENT)
        $quote->send();

        // Now try to add another line - should throw exception
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cannot add line to a non-draft quote');
        $quote->addLine(new QuoteLine('Service B', 2, new Amount(200)));
    }

    public function testSendQuote(): void
    {
        $quote = Quote::create(
            PersonId::generate(),
            'QT-2024-001',
            new \DateTimeImmutable('+30 days')
        );
        
        $line = new QuoteLine('Service A', 1, new Amount(100));
        $quote->addLine($line);
        $quote->send();

        $this->assertEquals(QuoteStatus::SENT, $quote->status());
    }

    public function testCannotSendEmptyQuote(): void
    {
        $quote = Quote::create(
            PersonId::generate(),
            'QT-2024-001',
            new \DateTimeImmutable('+30 days')
        );

        $this->expectException(\DomainException::class);
        $quote->send();
    }

    public function testAcceptQuote(): void
    {
        $quote = Quote::create(
            PersonId::generate(),
            'QT-2024-001',
            new \DateTimeImmutable('+30 days')
        );
        
        $line = new QuoteLine('Service A', 1, new Amount(100));
        $quote->addLine($line);
        $quote->send();
        $quote->accept();

        $this->assertEquals(QuoteStatus::ACCEPTED, $quote->status());
    }

    public function testTotalAmount(): void
    {
        $quote = Quote::create(
            PersonId::generate(),
            'QT-2024-001',
            new \DateTimeImmutable('+30 days')
        );

        $quote->addLine(new QuoteLine('Service A', 2, new Amount(50)));
        $quote->addLine(new QuoteLine('Service B', 1, new Amount(100)));

        $this->assertEquals(200, $quote->totalAmount()->value());
    }
}
