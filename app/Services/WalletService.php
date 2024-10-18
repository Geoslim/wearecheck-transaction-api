<?php

namespace App\Services;

use App\Models\User;

class WalletService
{
    public function createWallet(User $user)
    {
        return $user->wallet()->create();
    }
}
