<?php

namespace Tests\Feature;

use Tests\TestCase;
use Carbon\Carbon;

class TransactionTest extends TestCase
{
    private const TRANSACTION_URL = '/api/transactions';
    private const STATISTICS_URL = '/api/statistics';
    private const AMOUNT = 12.33;

    public function test_it_creates_a_transaction()
    {
        $response = $this->postJson(self::TRANSACTION_URL, [
            'amount' => self::AMOUNT,
            'timestamp' => Carbon::now('UTC')->format('Y-m-d\TH:i:s.v\Z'),
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
            'amount' => self::AMOUNT,
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
            'amount' => self::AMOUNT,
            'timestamp' => 'invalid_timestamp',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['timestamp']);
    }

    public function test_it_returns_statistics_for_last_60_seconds()
{
    // Criar transações de teste
    $transaction1 = factory(Transaction::class)->create([
        'amount' => 100,
        'timestamp' => Carbon::now()->subSeconds(30)->toISOString(),
    ]);

    $transaction2 = factory(Transaction::class)->create([
        'amount' => 200,
        'timestamp' => Carbon::now()->subSeconds(10)->toISOString(),
    ]);

    // Transação fora da janela de 60 segundos
    $transaction3 = factory(Transaction::class)->create([
        'amount' => 300,
        'timestamp' => Carbon::now()->subSeconds(70)->toISOString(),
    ]);

    $response = $this->getJson('/api/statistics');

    $response->assertStatus(200)
             ->assertJson([
                 'sum' => 300,
                 'avg' => 150,
                 'max' => 200,
                 'min' => 100,
                 'count' => 2,
             ]);
}

}
