<?php

namespace App\Services;

use App\Exceptions\WalletException;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Model;

class WalletService
{
    /**
     * @param User $user
     */
    public function createWallet(User $user)
    {
        return $user->wallet()->create();
    }

    /**
     * @param int|string $userId
     * @return mixed
     */
    public function walletQuery(int|string $userId)
    {
        return Wallet::query()->whereUserId($userId);
    }

    /**
     * @param Wallet $wallet
     * @param int|float $amount
     * @return void
     */
    public function debitWallet(Wallet $wallet, int|float $amount): void
    {
        $wallet->decrement('balance', $amount);
    }

    /**
     * @param Wallet $wallet
     * @param int|float $amount
     * @return void
     */
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
        if ($wallet->balance < $amount) {
            throw new WalletException(
                'Insufficient funds in your wallet for this transaction.'
            );
        }
    }
}
