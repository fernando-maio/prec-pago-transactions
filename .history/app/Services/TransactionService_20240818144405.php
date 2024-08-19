<?php

namespace App\Services;

use App\Interfaces\TransactionServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class TransactionService implements TransactionServiceInterface
{
    private $transactions;

    public function __construct()
    {
        $this->transactions = collect(Cache::get('transactions', []));
    }

    public function storeTransaction(Request $request): int
    {
        $transactionTime = Carbon::parse($request->timestamp);
        $now = Carbon::now('UTC');

        if ($transactionTime->gt($now)) {
            return 422;
        }

        if ($now->diffInSeconds($transactionTime) > 60) {
            return 204;
        }

        $transaction = [
            'amount' => (float) number_format($request->amount, 2, '.', ''),
            'timestamp' => $transactionTime
        ];

        $this->transactions->push([
            'amount' => (float) number_format($request->amount, 2, '.', ''),
            'timestamp' => $transactionTime
        ]);

        Cache::put('transactions', $this->transactions->toArray(), 60);

        return 201;
    }

    public function getStatistics(): array
    {
        $this->cleanupOldTransactions();

        $count = $this->transactions->count();
        $sum = $this->transactions->sum('amount');
        $avg = $count > 0 ? $sum / $count : 0;
        $max = $this->transactions->max('amount');
        $min = $this->transactions->min('amount');

        return [
            'sum' => number_format($sum, 2, '.', ''),
            'avg' => number_format($avg, 2, '.', ''),
            'max' => number_format($max, 2, '.', ''),
            'min' => number_format($min, 2, '.', ''),
            'count' => $count
        ];
    }

    public function deleteAllTransactions(): void
    {
        Cache::forget('transactions');
    }

    private function cleanupOldTransactions()
    {
        $now = Carbon::now('UTC');
        $this->transactions = $this->transactions->filter(function ($transaction) use ($now) {
            return $now->diffInSeconds($transaction['timestamp']) <= 60;
        });

        Cache::put('transactions', $this->transactions->toArray(), 60);
    }
}
