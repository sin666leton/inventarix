<?php

use App\Http\Controllers\api\V2\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('transactions', TransactionController::class)->only('show');
});