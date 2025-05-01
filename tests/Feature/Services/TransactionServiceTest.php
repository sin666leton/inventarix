<?php

namespace Tests\Feature\Services;

use App\DTOs\TransactionDTO;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\TransactionNotFound;
use App\Models\Category;
use App\Models\Item;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use Tests\TestCase;

class TransactionServiceTest extends TestCase
{
    protected $transactionService;

    protected $category;

    protected $item;

    protected $userTest;

    use RefreshDatabase;
    
    protected function setUp(): void
    {
        parent::setUp();

        /** @var \App\Services\TransactionService */
        $this->transactionService = $this->app->make(\App\Services\TransactionService::class);

        $this->category = Category::create(['name' => 'Cloth', 'description' => null]);
        $this->item = Item::create([
            'category_id' => $this->category->id,
            'name' => 'TEST',
            'code' => '#TSDT2300',
            'stock' => 50
        ]);

        $role = Role::create([
            'name' => 'admin'
        ]);

        $this->userTest = User::create([
            'role_id' => $role->id,
            'email' => 'testin@gmail.com',
            'name' => 'Testing',
            'password' => Hash::make('password')
        ]);

        Cache::put("item_".$this->item->id, $this->item, 3600);
    }

    public function test_find_transaction_throws_ModelNotFoundException_with_invalid_id()
    {
        $this->expectException(TransactionNotFound::class);
        $this->expectExceptionMessage('Transaction Not Found.');
        $this->expectExceptionCode(404);

        $this->transactionService->findTransaction(999);
    }

    public function test_find_transcation_should_cache_return_Transaction_with_valid_id()
    {
        $transaction = Transaction::create([
            'user_id' => $this->userTest->id,
            'item_id' => $this->item->id,
            'type' => 'in',
            'quantity' => 10
        ]);

        $result = $this->transactionService->findTransaction($transaction->id);

        $this->assertInstanceOf(Transaction::class, $result);
        $this->assertInstanceOf(Transaction::class, Cache::get("transaction_".$transaction->id));
    }

    public function test_create_transaction_throws_ModelNotException_with_invalid_item_id()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Item Not Found.');
        $this->expectExceptionCode(404);

        $dto = new TransactionDTO(999, 1, 'in', 10, null);

        $this->transactionService->createTransaction($dto);
    }

    public function test_create_transaction_throws_InsufficientStockException_with_invalid_stock_when_transaction_type_is_out()
    {
        $this->expectException(InsufficientStockException::class);
        $this->expectExceptionMessage('Insufficient Stock.');
        $this->expectExceptionCode(422);

        $dto = new TransactionDTO($this->item->id, 1, 'out', 999);

        $this->transactionService->createTransaction($dto);

        $this->assertDatabaseHas('items', [
            'id' => $this->item->id,
            'stock' => 50
        ]);
    }

    public function test_create_transaction_with_valid_data_and_return_Transaction_when_transaction_type_is_out()
    {
        $dto = new TransactionDTO($this->item->id, $this->userTest->id, 'out', 10);

        $result = $this->transactionService->createTransaction($dto);

        $this->assertEquals(null, Cache::get("item_$dto->item_id"));
        $this->assertInstanceOf(Transaction::class, $result);
        $this->assertDatabaseHas('items', [
            'id' => $dto->item_id,
            'stock' => 40
        ]);
        $this->assertDatabaseHas('transactions', [
            'id' => $result->id,
            'type' => 'out',
            'quantity' => 10
        ]);
    }

    public function test_create_transaction_with_valid_data_and_return_Transaction_when_transaction_type_is_in()
    {
        $dto = new TransactionDTO($this->item->id, $this->userTest->id, 'in', 100);

        $result = $this->transactionService->createTransaction($dto);

        $this->assertEquals(null, Cache::get("item_$dto->item_id"));
        $this->assertInstanceOf(Transaction::class, $result);
        $this->assertDatabaseHas('items', [
            'id' => $dto->item_id,
            'stock' => 150
        ]);
        $this->assertDatabaseHas('transactions', [
            'id' => $result->id,
            'type' => 'in',
            'quantity' => 100
        ]);
    }

    public function test_delete_transaction_throws_ModelNotFoundException_with_invalid_id()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Transaction Not Found.');
        $this->expectExceptionCode(404);

        $this->transactionService->deleteTransaction(999);
    }

    public function test_delete_transaction_should_forget_cache_and_increment_stock_when_type_is_out()
    {
        $dto = new TransactionDTO($this->item->id, $this->userTest->id, 'out', 45);

        $result = $this->transactionService->createTransaction($dto);

        $this->assertDatabaseHas('items', [
            'id' => $this->item->id,
            'stock' => 5
        ]);

        $this->transactionService->deleteTransaction($result->id);

        $this->assertEquals(null, Cache::get("item_$dto->item_id"));
        $this->assertEquals(null, Cache::get("transaction_$dto->item_id"));
        $this->assertInstanceOf(Transaction::class, $result);
        $this->assertDatabaseHas('items', [
            'id' => $dto->item_id,
            'stock' => 50
        ]);
        $this->assertDatabaseMissing('transactions', [
            'type' => 'out',
            'quantity' => 45
        ]);
    }

    public function test_delete_transaction_throws_InsufficientStockException_when_type_is_in()
    {
        $this->expectException(InsufficientStockException::class);
        $this->expectExceptionMessage("Insufficient Stock.");
        $this->expectExceptionCode(422);

        $dtoIN = new TransactionDTO($this->item->id, $this->userTest->id, 'in', 30);
        $dtoOUT = new TransactionDTO($this->item->id, $this->userTest->id, 'out', 70);

        $resultIN = $this->transactionService->createTransaction($dtoIN);

        $this->assertDatabaseHas('items', [
            'id' => $this->item->id,
            'stock' => 80
        ]);

        $this->transactionService->createTransaction($dtoOUT);

        $this->assertDatabaseHas('items', [
            'id' => $this->item->id,
            'stock' => 10
        ]);

        $this->transactionService->deleteTransaction($resultIN->id);

        $this->assertDatabaseHas('items', [
            'id' => $this->item->id,
            'stock' => 10
        ]);
    }

    public function test_delete_transaction_should_forget_cache_and_increment_stock_when_type_is_in()
    {
        $dtoIN = new TransactionDTO($this->item->id, $this->userTest->id, 'in', 30);
        $dtoOUT = new TransactionDTO($this->item->id, $this->userTest->id, 'out', 10);

        $resultIN = $this->transactionService->createTransaction($dtoIN);

        $this->assertDatabaseHas('items', [
            'id' => $this->item->id,
            'stock' => 80
        ]);

        $this->transactionService->createTransaction($dtoOUT);

        $this->assertDatabaseHas('items', [
            'id' => $this->item->id,
            'stock' => 70
        ]);

        $result = $this->transactionService->deleteTransaction($resultIN->id);

        $this->assertTrue($result);
        $this->assertEquals(null, Cache::get("item_$dtoIN->item_id"));
        $this->assertEquals(null, Cache::get("transaction_$resultIN->id"));
        $this->assertDatabaseHas('items', [
            'id' => $this->item->id,
            'stock' => 40
        ]);
    }

    public function test_paginate_transaction_and_return_LengthAwarePaginator()
    {
        $dto = new TransactionDTO($this->item->id, $this->userTest->id, 'out', 70);

        Transaction::insert([
            $dto->toArray(),
            $dto->toArray(),
            $dto->toArray(),
        ]);

        $result = $this->transactionService->paginateTransaction();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }
}
