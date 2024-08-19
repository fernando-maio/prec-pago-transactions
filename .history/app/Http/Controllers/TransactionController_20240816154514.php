<?php

namespace App\Http\Controllers;

use App\Interfaces\TransactionServiceInterface;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    private $transactionService;

    public function __construct(TransactionServiceInterface $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function store(Request $request)
    {
        $status = $this->transactionService->storeTransaction($request);
        return response()->json([], $status);
    }

    public function statistics()
    {
        dd(1);
        $statistics = $this->transactionService->getStatistics();
        return response()->json($statistics);
    }

    public function destroy()
    {
        $this->transactionService->deleteAllTransactions();
        return response()->json([], 204);
    }
}
