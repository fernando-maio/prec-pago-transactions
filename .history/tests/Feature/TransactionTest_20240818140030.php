<?php

namespace Tests\Feature;

use Tests\TestCase;
use Carbon\Carbon;

class TransactionTest extends TestCase
{
    private const TRANSACTION_URL = '/api/transactions';
    private const STATISTICS_URL = '/api/statistics';

    public function test_it_creates_a_transaction()
    {
        $response = $this->postJson(TRANSACTION_URL, [
            'amount' => '12.33',
            'timestamp' => Carbon::now('UTC')->toISOString(),
        ]);

        $response->assertStatus(201);
    }

    public function test_it_returns_statistics()
    {
        $this->getJson(TRANSACTION_URL, [
            'amount' => '10.00',
            'timestamp' => Carbon::now('UTC')->toISOString(),
        ]);

        $response = $this->getJson('/api/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'sum', 'avg', 'max', 'min', 'count'
            ]);
    }

    public function test_it_deletes_all_transactions()
    {
        $this->deleteJson(TRANSACTION_URL)
            ->assertStatus(204);
    }
}
