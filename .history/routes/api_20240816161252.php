<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'api'
], function () {
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/statistics', [TransactionController::class, 'statistics']);
    Route::delete('/transactions', [TransactionController::class, 'destroy']);