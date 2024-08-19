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

        $this->transactions->push($transaction);
        Cache::put('transactions', $this->transactions->toArray(), 60);

        return 201;
    }

    public function getStatistics(): array
    {
        $statistics = $this->transactions->reduce(function ($carry, $item) {
            $carry['sum'] += $item['amount'];
            $carry['max'] = max($carry['max'], $item['amount']);
            $carry['min'] = min($carry['min'], $item['amount']);
            $carry['count'] += 1;
            return $carry;
        }, [
            'sum' => 0,
            'max' => PHP_FLOAT_MIN,
            'min' => PHP_FLOAT_MAX,
            'count' => 0
        ]);

        $statistics['avg'] = $statistics['count'] > 0 ? $statistics['sum'] / $statistics['count'] : 0;

        return new StatisticsResource((object)$statistics);
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
