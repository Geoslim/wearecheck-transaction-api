<?php

namespace App\Services;

use App\Enums\Status;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;
use Ramsey\Uuid\UuidInterface;

class TransactionService
{
    public function generateReference(): UuidInterface
    {
        return Str::uuid();
    }

    public function checkDuplicateTransaction(string $userId, float $amount): bool
    {
        return Transaction::whereUserId($userId)
            ->whereType(TransactionType::DEBIT->value)
            ->whereStatus(Status::PENDING->value)
            ->whereAmount($amount)
            ->where('created_at', '>', now()->subMinute())
            ->exists();
    }

    public function createTransaction(array $data, User|Authenticatable $user)
    {

    }

}
