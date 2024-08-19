<?php

namespace Tests\Feature;

use Tests\TestCase;
use Carbon\Carbon;

class TransactionTest extends TestCase
{
    public function test_it_creates_a_transaction()
    {
        $response = $this->postJson('/api/transactions', [
            'amount' => '12.33',
            'timestamp' => Carbon::now('UTC')->toISOString(),
        ]);

        $response->assertStatus(201);
    }

    public function test_it_returns_statistics()
    {
        $this->postJson('/api/transactions', [
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
        $this->deleteJson('/api/transactions')
            ->assertStatus(204);
    }
}
