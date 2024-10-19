<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\WalletResource;
use App\Services\WalletService;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
    use JsonResponseTrait;

    public function __construct(public WalletService $walletService)
    {
    }

    public function index(Request $request): JsonResponse
    {
       try {
           $wallet = $this->walletService->walletQuery($request->user()->id)->first();

           return $this->successResponse(
               WalletResource::make($wallet)
           );
       } catch (\Exception $e) {
           Log::error('Unable to fetch user\'s wallet: ', [$e]);
           return $this->error();
       }
    }
}
