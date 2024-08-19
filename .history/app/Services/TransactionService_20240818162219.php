<?php

namespace App\Services;

use App\Http\Resources\StatisticsResource;
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

        if (abs($now->diffInSeconds($transactionTime)) > 60) {
            return 204;
        }

        $transaction = [
            'amount' => (float) number_format($request->amount, 2, '.', ''),
            'timestamp' => $transactionTime
        ];

        $this->transactions->push($transaction);
        Cache::put('transactions', $this->transactions->toArray());

        return 201;
    }

    public function getStatistics(): StatisticsResource
    {
        $sum = round($this->transactions->sum('amount'), 2, PHP_ROUND_HALF_UP);
        $count = $this->transactions->count();
        $avg = $count > 0 ? $sum / $count : 0;
        $max = round($this->transactions->max('amount'), 2, PHP_ROUND_HALF_UP) ?? 0;
        $min = round($this->transactions->min('amount'), 2, PHP_ROUND_HALF_UP) ?? 0;

        return new StatisticsResource((object)[
            'sum' => $sum,
            'avg' => $avg,
            'max' => $max,
            'min' => $min,
            'count' => $count
        ]);
    }

    public function deleteAllTransactions(): void
    {
        Cache::forget('transactions');
    }
}
