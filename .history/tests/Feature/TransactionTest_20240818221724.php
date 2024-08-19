<?php

namespace Tests\Feature;

use App\Services\TransactionService;
use Tests\TestCase;
use Carbon\Carbon;

class TransactionTest extends TestCase
{
    private const TRANSACTION_URL = '/api/transactions';
    private const STATISTICS_URL = '/api/statistics';
    private const AMOUNT = 12.33;

    private $transactionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transactionService = new TransactionServicee();
        Cache::forget('transactions');
    }

    public function test_it_stores_a_transaction()
    {
        $request = new Request([
            'amount' => 12.33,
            'timestamp' => Carbon::now('UTC')->format('Y-m-d\TH:i:s.v\Z'),
        ]);

        $response = $this->transactionService->storeTransaction($request);

        $this->assertEquals(201, $response);
        $this->assertCount(1, Cache::get('transactions'));
    }

    public function test_it_returns_422_if_transaction_time_is_in_the_future()
    {
        $request = new Request([
            'amount' => 12.33,
            'timestamp' => Carbon::now('UTC')->addMinute()->format('Y-m-d\TH:i:s.v\Z'),
        ]);

        $response = $this->transactionService->storeTransaction($request);

        $this->assertEquals(422, $response);
        $this->assertCount(0, Cache::get('transactions'));
    }

    public function test_it_returns_204_if_transaction_time_is_more_than_60_seconds_ago()
    {
        $request = new Request([
            'amount' => 12.33,
            'timestamp' => Carbon::now('UTC')->subMinutes(2)->format('Y-m-d\TH:i:s.v\Z'),
        ]);

        $response = $this->transactionService->storeTransaction($request);

        $this->assertEquals(204, $response);
        $this->assertCount(0, Cache::get('transactions'));
    }

    public function test_it_returns_statistics()
    {
        $request = new Request([
            'amount' => 12.33,
            'timestamp' => Carbon::now('UTC')->format('Y-m-d\TH:i:s.v\Z'),
        ]);

        $this->transactionService->storeTransaction($request);

        $statistics = $this->transactionService->getStatistics();

        $this->assertEquals(12.33, $statistics->sum);
        $this->assertEquals(12.33, $statistics->avg);
        $this->assertEquals(12.33, $statistics->max);
        $this->assertEquals(12.33, $statistics->min);
        $this->assertEquals(1, $statistics->count);
    }

    public function test_it_returns_empty_statistics_if_no_transactions_within_the_last_60_seconds()
    {
        $statistics = $this->transactionService->getStatistics();

        $this->assertEquals(0, $statistics->sum);
        $this->assertEquals(0, $statistics->avg);
        $this->assertEquals(0, $statistics->max);
        $this->assertEquals(0, $statistics->min);
        $this->assertEquals(0, $statistics->count);
    }

    public function test_it_deletes_all_transactions()
    {
        $request = new Request([
            'amount' => 12.33,
            'timestamp' => Carbon::now('UTC')->format('Y-m-d\TH:i:s.v\Z'),
        ]);

        $this->transactionService->storeTransaction($request);

        $this->transactionService->deleteAllTransactions();

        $this->assertCount(0, Cache::get('transactions'));
    }
}