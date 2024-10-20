<?php

namespace Tests;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BaseTestCase extends TestCase
{
    use RefreshDatabase;

    protected function createUserWithWallet(int $balance): User
    {
        return User::factory()->hasWallet(['balance' => $balance])->create();
    }
}
