<?php

namespace App\Http\Controllers\API\v1;

use App\Exceptions\WalletException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTransactionRequest;
use App\Services\TransactionService;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    use JsonResponseTrait;

    public function __construct(public TransactionService $transactionService)
    {
    }

    public function createTransaction(CreateTransactionRequest $request)
    {

    }
}
