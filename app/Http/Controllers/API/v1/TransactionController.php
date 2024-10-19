<?php

namespace App\Http\Controllers\API\v1;

use App\Exceptions\TransactionException;
use App\Exceptions\WalletException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Services\TransactionService;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class TransactionController extends Controller
{
    use JsonResponseTrait;

    public function __construct(public TransactionService $transactionService)
    {
    }

    /**
     * @param CreateTransactionRequest $request
     * @return JsonResponse
     * @throws WalletException|TransactionException|Throwable
     */
    public function createTransaction(CreateTransactionRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $transaction =  $this->transactionService->createTransaction(
                $request->validated(),
                $request->user()->id
            );
            DB::commit();

            return $this->successResponse(TransactionResource::make($transaction));
        } catch (WalletException|TransactionException $e) {
            DB::rollBack();
            return $this->error($e->getMessage());
        } catch (\Exception $e) {
            Log::error('create transaction error:: ', [$e]);
            DB::rollBack();
            return $this->error();
        }
    }
}
