<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['jsonOnly'])->group(function () {
    Route::prefix('/v1')
        ->name('v1.')
        ->group(base_path('routes/v1/api.php'));
});

Route::middleware(['jsonOnly'])->group(function () {
    Route::prefix('/v2')
        ->name('v2.')
        ->group(base_path('routes/v2/api.php'));
});

Route::fallback(function () {
    return response()->json([
        'message' => 'Route Not Exists.'
    ], 404);
});