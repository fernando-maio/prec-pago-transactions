<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface TransactionServiceInterface
{
    public function storeTransaction(Request $request): int;
    public function getStatistics(): array;
    public function deleteAllTransactions(): void;
}
