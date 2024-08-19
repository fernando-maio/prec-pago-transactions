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
            'timestamp' => $transactionTime->toDateTimeString()
        ];

        $this->transactions->push($transaction);
        Cache::put('transactions', $this->transactions->toArray());

        return 201;
    }

    public function getStatistics(): StatisticsResource
    {
        $now = Carbon::now();
        $sixtySecondsAgo = $now->subSeconds(60);
        $lastSeconds = $this->transactions->filter(function ($transaction) use ($now) {
            return Carbon::parse($transaction['timestamp'])->greaterThanOrEqualTo($now->subSeconds(60));
        });

        dd($lastSeconds);

        if ($lastSeconds->isEmpty()) {
            return new StatisticsResource((object)[
                'sum' => 0,
                'avg' => 0,
                'max' => 0,
                'min' => 0,
                'count' => 0
            ]);
        }

        $sum = $lastSeconds->sum('amount');
        $count = $lastSeconds->count();
        $avg = $count > 0 ? $sum / $count : 0;
        $max = $lastSeconds->max('amount');
        $min = $lastSeconds->min('amount');

        return new StatisticsResource((object)[
            'sum' => number_format(round($sum, 2, PHP_ROUND_HALF_UP), 2),
            'avg' => number_format(round($avg, 2, PHP_ROUND_HALF_UP), 2),
            'max' => number_format(round($max, 2, PHP_ROUND_HALF_UP), 2),
            'min' => number_format(round($min, 2, PHP_ROUND_HALF_UP), 2),
            'count' => $count
        ]);
    }

    public function deleteAllTransactions(): void
    {
        Cache::forget('transactions');
    }
}
