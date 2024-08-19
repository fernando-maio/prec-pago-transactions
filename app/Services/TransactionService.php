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
        $last60Seconds = $this->transactions->filter(function ($transaction) use ($sixtySecondsAgo) {
            $transactionTime = Carbon::parse($transaction['timestamp']);
            return $transactionTime->greaterThanOrEqualTo($sixtySecondsAgo);
        });
    
        if ($last60Seconds->isEmpty()) {
            return new StatisticsResource((object)[
                'sum' => number_format(0, 2),
                'avg' => number_format(0, 2),
                'max' => number_format(0, 2),
                'min' => number_format(0, 2),
                'count' => 0
            ]);
        }
    
        $sum = $last60Seconds->sum('amount');
        $count = $last60Seconds->count();
        $avg = $count > 0 ? $sum / $count : 0;
        $max = $last60Seconds->max('amount');
        $min = $last60Seconds->min('amount');

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
