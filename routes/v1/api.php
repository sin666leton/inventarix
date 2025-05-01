<?php

use App\Http\Controllers\api\V1\AuthController;
use App\Http\Controllers\api\V1\CategoryController;
use App\Http\Controllers\api\V1\ItemController;
use App\Http\Controllers\api\V1\StaffController;
use App\Http\Controllers\api\V1\TransactionController;
use App\Http\Controllers\api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::name('auth.')->controller(AuthController::class)->group(function () {
    Route::post('/admin/login', 'loginAdmin')->name('admin.login');
    Route::post('/staff/login', 'loginStaff')->name('staff.login');
    Route::delete('/admin/logout', 'logout')->name('admin.logout')->middleware('auth:sanctum');
    Route::delete('/staff/logout', 'logout')->name('staff.logout')->middleware('auth:sanctum');
});

Route::name('user.')->controller(UserController::class)->middleware('auth:sanctum')->group(function () {
    Route::put('/user/change_email', 'changeEmail')->name('change.email');
    Route::put('/user/change_name', 'changeName')->name('change.name');
    Route::put('/user/change_password', 'changePassword')->name('change.password');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/categories/all', [CategoryController::class, 'all']);
    Route::apiResource('categories', CategoryController::class);

    Route::apiResource('items', ItemController::class);
    Route::apiResource('transactions', TransactionController::class)->except('update');
    Route::apiResource('staff', StaffController::class);
});