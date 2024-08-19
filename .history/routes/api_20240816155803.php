<?php

use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;



Route::group([
    'middleware' => 'api'
], function () {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});