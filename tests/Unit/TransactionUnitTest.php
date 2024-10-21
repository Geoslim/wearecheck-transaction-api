<?php

namespace Tests\Unit;

use App\Enums\Status;
use App\Enums\TransactionType;
use App\Exceptions\TransactionException;
use App\Models\Transaction;
use App\Services\TransactionService;
use App\Services\WalletService;
use Mockery;
use Tests\BaseTestCase;

class TransactionUnitTest extends BaseTestCase
{
    protected TransactionService $transactionService;

    public function setUp(): void
    {
        parent::setUp();
        $this->walletServiceMock = Mockery::mock(WalletService::class);
        $this->transactionService = new TransactionService( $this->walletServiceMock);
    }

    public function testThatADebitTransactionIsCreated()
    {
        $wallet = $this->createUserWithWallet(100)->wallet;
        $this->walletServiceMock->shouldReceive('walletQuery')->andReturn($wallet);
        $this->walletServiceMock->shouldReceive('validateWalletBalance')->once();
        $this->walletServiceMock->shouldReceive('debitWallet')->once();

        // Transaction data
        $data = [
            'type' => TransactionType::DEBIT->value,
            'amount' => 50
        ];

        $transaction = $this->transactionService->createTransaction($data, $wallet->user_id);

        $this->assertEquals(TransactionType::DEBIT->value, $transaction->type);
        $this->assertEquals(50, $transaction->amount);
        $this->assertEquals(Status::PENDING->value, $transaction->status);
    }

    public function testThatACreditTransactionIsCreated()
    {
        $wallet = $this->createUserWithWallet(100)->wallet;
        $this->walletServiceMock->shouldReceive('walletQuery')->andReturn($wallet);
        $this->walletServiceMock->shouldReceive('creditWallet')->once();

        // Transaction data
        $data = [
            'type' => TransactionType::CREDIT->value,
            'amount' => 50,
            'reference' => $this->transactionService->generateReference()
        ];

        $transaction = $this->transactionService->createTransaction($data, $wallet->user_id);

        $this->assertEquals(TransactionType::CREDIT->value, $transaction->type);
        $this->assertEquals(50, $transaction->amount);
        $this->assertEquals(Status::SUCCESS->value, $transaction->status);
    }

    public function testThatMethodThrowsAnExceptionForInvalidTransactionType()
    {
        $wallet = $this->createUserWithWallet(100)->wallet;
        $this->walletServiceMock->shouldReceive('walletQuery')->andReturn($wallet);

        $data = [
            'type' => 'INVALID_TYPE',
            'amount' => 50
        ];

        $this->expectException(TransactionException::class);

        $this->transactionService->createTransaction($data, $wallet->user_id);
    }

    public function testThatMethodDetectsDuplicateTransactionForDebit()
    {
        $user = $this->createUserWithWallet(500);
        $wallet = $user->wallet;

        Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'amount' => 100,
            'reference' => $this->transactionService->generateReference(),
            'type' => TransactionType::DEBIT->value,
            'status' => Status::PENDING->value,
            'created_at' => now()->subSeconds(30) // within 1 minute
        ]);

        $this->expectException(TransactionException::class);

        $this->transactionService->abortIfDuplicateTransactionIsFound($wallet->id, 100);
    }

    public function testThatMethodDetectsDuplicateReferenceForCredit()
    {
        $user = $this->createUserWithWallet(500);
        $wallet = $user->wallet;

        $reference = $this->transactionService->generateReference();

        Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'amount' => 100,
            'reference' => $reference,
            'type' => TransactionType::CREDIT->value,
            'status' => Status::SUCCESS->value,
            'created_at' => now()->subSeconds(30) // within 1 minute
        ]);

        $this->expectException(TransactionException::class);

        $this->transactionService->abortIfDuplicateReferenceIsFound($reference);
    }
}
