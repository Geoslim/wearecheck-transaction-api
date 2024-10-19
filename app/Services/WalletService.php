<?php

namespace App\Services;

use App\Exceptions\WalletException;
use App\Models\User;
use App\Models\Wallet;

class WalletService
{
    public function createWallet(User $user)
    {
        return $user->wallet()->create();
    }

    public function walletQuery(int|string $userId)
    {
        return Wallet::query()->whereUserId($userId);
    }

    public function debitWallet(Wallet $wallet, int|float $amount): void
    {
        $wallet->decrement('balance', $amount);
    }

    public function creditWallet(Wallet $wallet, int|float $amount): void
    {
        $wallet->increment('balance', $amount);
    }

    /**
     * @param Wallet $wallet
     * @param int|float $amount
     * @return void
     * @throws WalletException
     */
    public function validateWalletBalance(Wallet $wallet, int|float $amount): void
    {
        if ($wallet->balance < $amount || $wallet->balance == 0) {
            throw new WalletException(
                'Insufficient funds in your wallet for this transaction.'
            );
        }
    }
}
