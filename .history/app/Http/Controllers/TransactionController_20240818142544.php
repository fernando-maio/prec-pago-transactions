<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Interfaces\TransactionServiceInterface;

class TransactionController extends Controller
{
    private $transactionService;

    public function __construct(TransactionServiceInterface $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function store(StoreTransactionRequest $request)
    {
        $status = $this->transactionService->storeTransaction($request);
        return response()->json([], $status);
    }

    public function statistics()
    {
        $statistics = $this->transactionService->getStatistics();
        return response()->json($statistics);
    }

    public function destroy()
    {
        $this->transactionService->deleteAllTransactions();
        return response()->json([], 204);
    }
}
