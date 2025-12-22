<?php

declare(strict_types=1);

namespace App\Banking\Domain\Service;

use App\Banking\Domain\AggregateRoot\BankAccount;
use App\Banking\Domain\Entity\BankTransaction;
use App\Banking\Domain\ValueObject\TransactionType;
use App\Billing\Domain\ValueObject\Amount;

/**
 * Domain Service for bank account balance calculations and validations.
 * 
 * This is a Domain Service because:
 * - It performs complex calculations involving transactions
 * - It encapsulates business rules about balances
 * - It's pure business logic
 */
final class BalanceCalculator
{
    /**
     * Calculate the current balance from a list of transactions.
     * 
     * @param BankTransaction[] $transactions The list of transactions
     * @return Amount The calculated balance
     */
    public function calculateFromTransactions(array $transactions): Amount
    {
        $balance = 0.0;

        foreach ($transactions as $transaction) {
            if (!$transaction instanceof BankTransaction) {
                throw new \InvalidArgumentException('All items must be BankTransaction instances');
            }

            if ($transaction->type() === TransactionType::CREDIT) {
                $balance += $transaction->amount()->value();
            } else {
                $balance -= $transaction->amount()->value();
            }
        }

        return new Amount($balance);
    }

    /**
     * Check if an account has sufficient balance for a debit.
     * 
     * @param BankAccount $account The bank account
     * @param Amount $amount The amount to debit
     * @return bool True if sufficient balance
     */
    public function hasSufficientBalance(BankAccount $account, Amount $amount): bool
    {
        return $account->balance()->value() >= $amount->value();
    }

    /**
     * Calculate the available balance considering pending transactions.
     * 
     * @param BankAccount $account The bank account
     * @param Amount $pendingAmount Amount of pending transactions
     * @return Amount The available balance
     */
    public function calculateAvailableBalance(BankAccount $account, Amount $pendingAmount): Amount
    {
        $available = $account->balance()->value() - $pendingAmount->value();
        return new Amount(max(0, $available), $account->balance()->currency());
    }

    /**
     * Check if account balance is below a warning threshold.
     * 
     * @param BankAccount $account The bank account
     * @param Amount $threshold The warning threshold
     * @return bool True if below threshold
     */
    public function isBelowThreshold(BankAccount $account, Amount $threshold): bool
    {
        return $account->balance()->value() < $threshold->value();
    }
}
