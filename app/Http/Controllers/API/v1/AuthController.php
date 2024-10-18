<?php

namespace App\Http\Controllers\API\v1;

use App\Exceptions\AuthException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use App\Traits\JsonResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuthController extends Controller
{
    use JsonResponseTrait;

    public function __construct(public AuthService $authService)
    {
    }

    /**
     * @param RegisterRequest $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $response = $this->authService->register($request->validated());
            return $this->successResponse($response);
        } catch (AuthException $e) {
            Log::error('User unable to sign up:: ', [$e]);
            DB::rollBack();
            return $this->error($e->getMessage());
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('User unable to sign up:: ', [$e]);
            return $this->error('Unable to complete your registration at this time. Kindly try again shortly.');
        }
    }

    /**
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $response = $this->authService->login($request->validated());
            return $this->successResponse($response);
        } catch (AuthException $e) {
            Log::error('User unable to sign in:: ', [$e]);
            return $this->error($e->getMessage());
        } catch (Exception $e) {
            Log::error('User unable to sign in:: ', [$e]);
            return $this->error('Unable to log in at this time. Kindly try again shortly.');
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logout($request->user());
            return $this->success('Successfully logged out');
        } catch (Exception $e) {
            Log::error('User unable to log out:: ', [$e]);
            return $this->error('Unable to log out at this time. Kindly try again shortly.');
        }
    }
}
