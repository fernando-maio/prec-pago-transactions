<?php

namespace App\Services;

use App\Http\Resources\StatisticsResource;
use App\Http\Resources\TransactionResource;
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

        $cacheKey = 'transaction_' . $transaction->id;
        Cache::put($cacheKey, $transaction);

        return response()->json([
            'success' => true,
            'data' => new TransactionResource($transaction),
        ], 201);
    }

    public function getStatistics(): StatisticsResource
    {
        $now = Carbon::now('UTC');
        $last60Seconds = $this->transactions->filter(function ($transaction) use ($now) {
            return Carbon::parse($transaction['timestamp'])->greaterThanOrEqualTo($now->subSeconds(60));
        });

        if ($last60Seconds->isEmpty()) {
            return new StatisticsResource((object)[
                'sum' => 0,
                'avg' => 0,
                'max' => 0,
                'min' => 0,
                'count' => 0
            ]);
        }

        $sum = $last60Seconds->sum('amount');
        $count = $last60Seconds->count();
        $avg = $count > 0 ? $sum / $count : 0;
        $max = $last60Seconds->max('amount');
        $min = $last60Seconds->min('amount');

        return new StatisticsResource((object)[
            'sum' => $sum,
            'avg' => $avg,
            'max' => $max,
            'min' => $min,
            'count' => $count
        ]);

        $sum = round($transactions->sum('amount'), 2, PHP_ROUND_HALF_UP);
        $count = $transactions->count();
        $avg = $count > 0 ? round(($sum / $count), 2, PHP_ROUND_HALF_UP) : 0;
        $max = round($transactions->max('amount'), 2, PHP_ROUND_HALF_UP) ?? 0;
        $min = round($transactions->min('amount'), 2, PHP_ROUND_HALF_UP) ?? 0;

        return new StatisticsResource((object)[
            'sum' => number_format($sum, 2),
            'avg' => number_format($avg, 2),
            'max' => number_format($max, 2),
            'min' => number_format($min, 2),
            'count' => $count
        ]);
    }

    public function deleteAllTransactions(): void
    {
        Cache::forget('transactions');
    }
}
