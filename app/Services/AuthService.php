<?php

namespace App\Services;

use App\Exceptions\AuthException;
use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class AuthService
{
    public function __construct(public WalletService $walletService)
    {
        //
    }

    /**
     * @param array $data
     * @return array
     * @throws Exception|Throwable
     */
    public function register(array $data): array
    {
        DB::beginTransaction();

        $user = User::create($data);

        // create default wallet
        $this->walletService->createWallet($user);

        $response['token'] = $user->createToken($user->email)->plainTextToken;
        $response['user'] = UserResource::make($user);

        DB::commit();

        return $response;
    }

    /**
     * @param array $data
     * @return array
     * @throws AuthException
     */
    public function login(array $data): array
    {
        $user = User::whereEmail($data['user_name'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw new AuthException('Access denied, please check your credentials');
        }

        $response['token'] = $user->createToken($user->email)->plainTextToken;
        $response['user'] = UserResource::make($user);

        return $response;
    }

    /**
     * @param User $user
     * @return void
     */
    public function logout(User $user): void
    {
        $user->tokens()->delete();
    }
}
