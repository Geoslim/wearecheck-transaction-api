<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Enums\TransactionType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\BaseTestCase;
use Tests\TestCase;

class TransactionFeatureTest extends BaseTestCase
{
    public function testThatUserCanDebitWalletSuccessfully(): void
    {
        $user = $this->createUserWithWallet(500);
        $wallet = $user->wallet;

        $data = [
            'type' => TransactionType::DEBIT->value,
            'amount' => 100,
        ];

        $response = $this->actingAs($user)->postJson('/api/v1/transactions/create', $data);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'status' => Status::PENDING->value,
                    'amount' => 100,
                ]
            ]);

        // Assert that wallet balance is reduced
        $this->assertEquals(400, $wallet->fresh()->balance);

        // Assert that a transaction record was created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'amount' => 100,
            'status' => Status::PENDING->value,
            'type' => TransactionType::DEBIT->value,
        ]);
    }

    public function testThatUserCanCreditWalletSuccessfully()
    {
        $user = $this->createUserWithWallet(100);
        $wallet = $user->wallet;

        $data = [
            'type' => TransactionType::CREDIT->value,
            'amount' => 200,
            'reference' => 'WE-ARE-CHECK|'. time()
        ];

        $response = $this->actingAs($user)->postJson('/api/v1/transactions/create', $data);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'status' => Status::SUCCESS->value,
                    'amount' => 200,
                ]
            ]);

        // Assert that wallet balance is increased
        $this->assertEquals(300, $wallet->fresh()->balance);

        // Assert that a transaction record was created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'amount' => 200,
            'status' => Status::SUCCESS->value,
            'type' => TransactionType::CREDIT->value,
        ]);
    }

    public function testThatTransactionFailsToDebitWalletWithInsufficientFunds()
    {
        $user = $this->createUserWithWallet(10);
        $wallet = $user->wallet;

        $data = [
            'type' => TransactionType::DEBIT->value,
            'amount' => 100,
        ];

        $response = $this->actingAs($user)->postJson('/api/v1/transactions/create', $data);

        $response->assertBadRequest()
            ->assertJson([
                'message' => 'Insufficient funds in your wallet for this transaction.'
            ]);

        // Assert that wallet balance did not change
        $this->assertEquals(10, $wallet->fresh()->balance);

        // Assert that no transaction was created
        $this->assertDatabaseMissing('transactions', [
            'user_id' => $user->id,
            'amount' => 100,
        ]);
    }

    public function testThatDuplicateDebitTransactionIsNotAllowed()
    {
        $user = $this->createUserWithWallet(200);

        $data = [
            'type' => TransactionType::DEBIT->value,
            'amount' => 100,
        ];

        // First transaction should succeed
        $response = $this->actingAs($user)->postJson('/api/v1/transactions/create', $data);
        $response->assertOk();

        // Second transaction within 1 minute should fail (duplicate)
        $response = $this->actingAs($user)->postJson('/api/v1/transactions/create', $data);

        $response->assertBadRequest()
            ->assertJson([
                'message' => 'A transaction with similar details exists. Please try again in a few minutes'
            ]);
    }

    public function testThatDuplicateReferenceForCreditTransactionIsNotAllowed()
    {
        $user = $this->createUserWithWallet(200);

        $data = [
            'type' => TransactionType::CREDIT->value,
            'amount' => 150,
            'reference' => 'WE-ARE-CHECK|1234567890'
        ];

        // First credit transaction should succeed
        $response = $this->actingAs($user)->postJson('/api/v1/transactions/create', $data);
        $response->assertOk();

        // Second transaction with the same reference should fail
        $response = $this->actingAs($user)->postJson('/api/v1/transactions/create', $data);

        $response->assertBadRequest()
            ->assertJson([
                'message' => 'A transaction with this reference already exists.'
            ]);
    }
}
