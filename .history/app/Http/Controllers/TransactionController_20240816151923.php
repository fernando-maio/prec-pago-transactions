<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class TransactionController extends Controller
{
    private $transactions;

    public function __construct()
    {
        $this->transactions = collect(Cache::get('transactions', []));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'timestamp' => 'required|date_format:Y-m-d\TH:i:s.v\Z'
        ]);

        $transactionTime = Carbon::parse($request->timestamp);
        $now = Carbon::now('UTC');

        if ($transactionTime->gt($now)) {
            return response()->json([], 422);
        }

        if ($now->diffInSeconds($transactionTime) > 60) {
            return response()->json([], 204);
        }

        $this->transactions->push([
            'amount' => (float) number_format($request->amount, 2, '.', ''),
            'timestamp' => $transactionTime
        ]);

        Cache::put('transactions', $this->transactions->toArray(), 60);

        return response()->json([], 201);
    }

    public function statistics()
    {
        $this->cleanupOldTransactions();

        $count = $this->transactions->count();
        $sum = $this->transactions->sum('amount');
        $avg = $count > 0 ? $sum / $count : 0;
        $max = $this->transactions->max('amount');
        $min = $this->transactions->min('amount');

        return response()->json([
            'sum' => number_format($sum, 2, '.', ''),
            'avg' => number_format($avg, 2, '.', ''),
            'max' => number_format($max, 2, '.', ''),
            'min' => number_format($min, 2, '.', ''),
            'count' => $count
        ]);
    }

    public function destroy()
    {
        Cache::forget('transactions');
        return response()->json([], 204);
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
