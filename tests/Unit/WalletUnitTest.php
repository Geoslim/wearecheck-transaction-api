<?php

namespace Tests\Unit;

use App\Exceptions\WalletException;
use App\Services\WalletService;
use Tests\BaseTestCase;

class WalletUnitTest extends BaseTestCase
{
    protected WalletService $walletService;

    public function setUp(): void
    {
        parent::setUp();
        $this->walletService = new WalletService();
    }

    public function testThatWalletIsDebitedSuccessfully()
    {
        $wallet = $this->createUserWithWallet(500)->wallet;

        $this->walletService->debitWallet($wallet, 100);

        $this->assertEquals(400, $wallet->fresh()->balance);
    }

    public function testThatWalletIsCreditedSuccessfully()
    {
        $wallet = $this->createUserWithWallet(500)->wallet;

        $this->walletService->creditWallet($wallet, 100);

        $this->assertEquals(600, $wallet->fresh()->balance);
    }

    public function testThatAnExceptionIsThrownWhenBalanceIsLow()
    {
        $wallet = $this->createUserWithWallet(10)->wallet;

        $this->expectException(WalletException::class);
        $this->expectExceptionMessage('Insufficient funds');

        $this->walletService->validateWalletBalance($wallet, 100);
    }
}
