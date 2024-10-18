<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\WalletResource;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
    use JsonResponseTrait;

    public function index(Request $request): JsonResponse
    {
       try {
           return $this->successResponse(
               WalletResource::make(
                   $request->user()->wallet
               )
           );
       } catch (\Exception $e) {
           Log::error('Unable to fetch user\'s wallet: ', [$e]);
           return $this->error();
       }
    }
}
