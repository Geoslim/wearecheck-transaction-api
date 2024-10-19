<?php

namespace App\Services;

use App\Enums\Status;
use App\Enums\TransactionType;
use App\Exceptions\TransactionException;
use App\Exceptions\WalletException;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Str;
use Ramsey\Uuid\UuidInterface;
use Throwable;

class TransactionService
{
    public function __construct(public WalletService $walletService)
    {
    }

    /**
     * @param array $data
     * @param int|string $userId
     * @return Transaction
     * @throws Throwable
     * @throws TransactionException|WalletException
     */
    public function createTransaction(array $data, int|string $userId): Transaction
    {
        // Lock the wallet for updates once at the beginning of each transaction type
        $wallet = $this->walletService->walletQuery($userId)->lockForUpdate()->first();

        return match ($data['type']) {
            TransactionType::DEBIT->value => $this->debitTransaction($wallet, $userId, $data),
            TransactionType::CREDIT->value => $this->creditTransaction($wallet, $userId, $data),
            default => throw new TransactionException('Invalid Transaction type specified.')
        };
    }

    /**
     * @param Wallet $wallet
     * @param int|string $userId
     * @param array $data
     * @return Transaction
     * @throws WalletException|TransactionException|Throwable
     */
    private function debitTransaction(Wallet $wallet, int|string $userId, array $data): Transaction
    {
        // Validate wallet balance and check for duplicate transaction
        $this->walletService->validateWalletBalance($wallet, $data['amount']);
        $this->abortIfDuplicateTransactionIsFound($wallet->id, $data['amount']);

        // Debit wallet
        $this->walletService->debitWallet($wallet, $data['amount']);

        // Generate reference and record transaction
        $data['reference'] = $this->generateReference();
        return $this->recordTransaction($wallet->id, $userId, $data, Status::PENDING->value);
    }

    /**
     * @param Wallet $wallet
     * @param int|string $userId
     * @param array $data
     * @return Transaction
     * @throws Throwable
     */
    private function creditTransaction(Wallet $wallet, int|string $userId, array $data): Transaction
    {
        // Abort if a duplicate reference is found to avoid double credit
        $this->abortIfDuplicateReferenceIsFound($data['reference']);
        // Credit the wallet and record transaction
        $this->walletService->creditWallet($wallet, $data['amount']);
        return $this->recordTransaction($wallet->id, $userId, $data, Status::SUCCESS->value);
    }

    public function generateReference(): UuidInterface
    {
        return Str::uuid();
    }

    /**
     * @param int|string $walletId
     * @param int|float $amount
     * @return void
     * @throws Throwable
     */
    public function abortIfDuplicateTransactionIsFound(int|string $walletId, int|float $amount): void
    {
        throw_if(
            $this->checkDuplicateTransaction($walletId, $amount),
            TransactionException::class,
            'A transaction with similar details exists. Please try again in a few minutes'
        );
    }


    /**
     * @param string $walletId
     * @param float $amount
     * @return bool
     */
    public function checkDuplicateTransaction(string $walletId, float $amount): bool
    {
        return Transaction::whereWalletId($walletId)
            ->whereType(TransactionType::DEBIT->value)
            ->whereStatus(Status::PENDING->value)
            ->whereAmount($amount)
            ->where('created_at', '>', now()->subMinute())
            ->exists();
    }

    /**
     * @param string $reference
     * @return void
     * @throws Throwable
     */
    public function abortIfDuplicateReferenceIsFound(string $reference): void
    {
        throw_if(
            Transaction::whereReference($reference)->whereStatus(Status::SUCCESS->value)->exists(),
            TransactionException::class,
            'A transaction with this reference already exists.'
        );
    }

    /**
     * @param int|string $walletId
     * @param int|string $userId
     * @param array $data
     * @param string $status
     * @return Transaction
     */
    private function recordTransaction(
        int|string $walletId,
        int|string $userId,
        array $data,
        string $status
    ): Transaction {
        return Transaction::create([
            'user_id' => $userId,
            'wallet_id' => $walletId,
            'type' => $data['type'],
            'reference' => $data['reference'],
            'amount' => $data['amount'],
            'status' => $status,
        ]);
    }
}
