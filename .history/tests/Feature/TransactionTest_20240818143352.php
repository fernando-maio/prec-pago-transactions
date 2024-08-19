<?php

namespace Tests\Feature;

use Tests\TestCase;
use Carbon\Carbon;

class TransactionTest extends TestCase
{
    private const TRANSACTION_URL = '/api/transactions';
    private const STATISTICS_URL = '/api/statistics';
    private const AMOUNT = '/api/statistics';

    public function test_it_creates_a_transaction()
    {
        $response = $this->postJson(self::TRANSACTION_URL, [
            'amount' => '12.33',
            'timestamp' => Carbon::now('UTC')->toISOString(),
        ]);

        $response->assertStatus(201);
    }

    public function test_it_returns_statistics()
    {
        $this->getJson(self::TRANSACTION_URL, []);

        $response = $this->getJson(self::STATISTICS_URL);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'sum',
                'avg',
                'max',
                'min',
                'count'
            ]);
    }

    public function test_it_deletes_all_transactions()
    {
        $this->deleteJson(self::TRANSACTION_URL)
            ->assertStatus(204);
    }

    public function test_it_requires_an_amount_and_timestamp()
    {
        $response = $this->postJson(self::TRANSACTION_URL, [
            'timestamp' => Carbon::now('UTC')->toISOString(),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);

        $response = $this->postJson(self::TRANSACTION_URL, [
            'amount' => '12.33',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['timestamp']);
    }

    public function test_it_requires_valid_amount_and_timestamp()
    {
        $response = $this->postJson(self::TRANSACTION_URL, [
            'amount' => 'invalid_amount',
            'timestamp' => Carbon::now('UTC')->toISOString(),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);

        $response = $this->postJson(self::TRANSACTION_URL, [
            'amount' => '12.33',
            'timestamp' => 'invalid_timestamp',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['timestamp']);
    }
}
