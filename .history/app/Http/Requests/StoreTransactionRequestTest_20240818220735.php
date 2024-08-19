<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\StoreTransactionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Exceptions\HttpResponseException;
use Tests\TestCase;

class StoreTransactionRequestTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_it_authorizes_the_request()
    {
        $request = new StoreTransactionRequest();

        $this->assertTrue($request->authorize());
    }

    public function test_it_validates_required_fields()
    {
        $request = new StoreTransactionRequest();

        $validator = $this->app['validator']->make([], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertEquals([
            'amount' => ['The amount field is required.'],
            'timestamp' => ['The timestamp field is required.'],
        ], $validator->errors()->messages());
    }

    public function test_it_validates_numeric_amount()
    {
        $request = new StoreTransactionRequest();

        $validator = $this->app['validator']->make(['amount' => 'invalid_amount'], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertEquals([
            'amount' => ['The amount must be a number.'],
        ], $validator->errors()->messages());
    }

    public function test_it_validates_date_format_timestamp()
    {
        $request = new StoreTransactionRequest();

        $validator = $this->app['validator']->make(['timestamp' => 'invalid_timestamp'], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertEquals([
            'timestamp' => ['The timestamp does not match the format Y-m-d\TH:i:s.v\Z.'],
        ], $validator->errors()->messages());
    }

    public function test_it_handles_failed_validation()
    {
        $request = new StoreTransactionRequest();

        $validator = $this->app['validator']->make([], $request->rules());

        $this->expectException(HttpResponseException::class);

        $request->failedValidation($validator);
    }
}