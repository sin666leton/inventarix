<?php

namespace Tests\Unit\Service;

use App\DTOs\TransactionDTO;
use App\Exceptions\InsufficientStockException;
use App\Models\Transaction;
use App\Services\ItemService;
use App\Services\TransactionService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;

class TransactionServiceTest extends TestCase
{
    private $transactionService;

    private $itemRepository;

    private $itemService;

    private $transactionRepository;

    protected function setUp(): void
    {
        parent::setUp();

        /** 
         * @var \App\Contracts\Transaction&Mockery\MockInterface
         **/
        $this->transactionRepository = Mockery::mock(\App\Contracts\Transaction::class);
    
        /** @var \App\Contracts\Item&Mockery\MockInterface */
        $this->itemRepository = Mockery::mock(\App\Contracts\Item::class);

        $this->itemService = new ItemService($this->itemRepository, Mockery::mock(\App\Contracts\Category::class));

        $this->transactionService = new TransactionService($this->itemService, $this->transactionRepository);
    }

    protected function tearDown(): void
    {
        Cache::swap(new \Illuminate\Cache\Repository(new \Illuminate\Cache\ArrayStore()));
        Mockery::close();

        parent::tearDown();
    }

    public function test_find_transaction_throws_ModelNotFoundException_with_invalid_id()
    {        
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Transaction Not Found.');
        $this->expectExceptionCode(404);

        $id = 999;

        $this->transactionRepository
            ->shouldReceive('find')
            ->once()
            ->with($id)
            ->andThrowExceptions([
                new ModelNotFoundException('Transaction Not Found.', 404)
            ]);

        Cache::shouldReceive('remember')
            ->once()
            ->with("transaction_$id", 3600, \Closure::class)
            ->andReturnUsing(function ($key, $ttl, $closure) {
                return $closure();
            });

        $this->transactionService->findTransaction($id);
    }

    public function test_find_transcation_should_cache_return_Transaction_with_valid_id()
    {
        $id = 1;

        $this->transactionRepository
            ->shouldReceive('find')
            ->once()
            ->with($id)
            ->andReturn(new Transaction());

        Cache::shouldReceive('remember')
            ->once()
            ->with("transaction_$id", 3600, \Closure::class)
            ->andReturnUsing(function ($key, $ttl, $closure) {
                return $closure();
            });

        $result = $this->transactionService->findTransaction($id);
    
        $this->assertInstanceOf(Transaction::class, $result);
    }

    public function test_create_transaction_throws_ModelNotException_with_invalid_item_id()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Item Not Found.');
        $this->expectExceptionCode(404);

        $dto = new TransactionDTO(999, 1, 'in', 10);
        
        DB::shouldReceive('beginTransaction');
        DB::shouldReceive('rollBack');

        $this->itemRepository
            ->shouldReceive('getStock')
            ->once()
            ->with($dto->item_id)
            ->andThrowExceptions([
                new ModelNotFoundException('Item Not Found.', 404)
            ]);

        $this->transactionService->createTransaction($dto);
    }

    public function test_create_transaction_throws_InvalidArgumentType_with_invalid_type()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid transaction type.");

        new TransactionDTO(
            1,
            1,
            'abcd',
            10
        );
    }

    public function test_create_transaction_throws_InsufficientStockException_with_invalid_stock_when_transaction_type_is_out()
    {
        Cache::spy();

        $this->expectException(InsufficientStockException::class);
        $this->expectExceptionCode(422);

        $dto = new TransactionDTO(1, 1, 'out', 999);

        $this->itemRepository
            ->shouldReceive('getStock')
            ->once()
            ->with($dto->item_id)
            ->andReturn(10);
        
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();

        $this->transactionService->createTransaction($dto);
    }

    public function test_create_transaction_with_valid_data_and_return_Transaction_when_transaction_type_is_out()
    {
        Cache::spy();

        $dto = new TransactionDTO(1, 1, 'out', 10);

        $this->itemRepository
            ->shouldReceive('getStock')
            ->once()
            ->with($dto->item_id)
            ->andReturn(100);
        
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();

        $this->itemRepository
            ->shouldReceive('updateStock')
            ->once()
            ->with($dto->item_id, 100 - $dto->quantity)
            ->andReturn(true);

        $this->transactionRepository
            ->shouldReceive('create')
            ->once()
            ->with($dto)
            ->andReturn(new Transaction($dto->toArray()));

        $result = $this->transactionService->createTransaction($dto);

        Cache::shouldHaveReceived('forget')
            ->once()
            ->with("item_$dto->item_id");

        $this->assertInstanceOf(Transaction::class, $result);
    }

    public function test_create_transaction_with_valid_data_and_return_Transaction_when_transaction_type_is_in()
    {
        Cache::spy();

        $dto = new TransactionDTO(1, 1, 'in', 10);

        $this->itemRepository
            ->shouldReceive('getStock')
            ->once()
            ->with($dto->item_id)
            ->andReturn(100);
        
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();

        $this->itemRepository
            ->shouldReceive('updateStock')
            ->once()
            ->with($dto->item_id, 100 + $dto->quantity)
            ->andReturn(true);

        $this->transactionRepository
            ->shouldReceive('create')
            ->once()
            ->with($dto)
            ->andReturn(new Transaction($dto->toArray()));

        $result = $this->transactionService->createTransaction($dto);

        Cache::shouldHaveReceived('forget')
            ->once()
            ->with("item_$dto->item_id");

        $this->assertInstanceOf(Transaction::class, $result);
    }

    public function test_delete_transaction_throws_ModelNotFoundException_with_invalid_id()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Transaction Not Found.');
        $this->expectExceptionCode(404);

        $id = 999;

        $this->transactionRepository
            ->shouldReceive('find')
            ->with($id)
            ->once()
            ->andThrowExceptions([
                new ModelNotFoundException('Transaction Not Found.', 404)
            ]);

        Cache::shouldReceive('remember')
            ->once()
            ->with("transaction_$id", 3600, \Closure::class)
            ->andReturnUsing(function ($key, $ttl, $closure) {
                return $closure();
            });

        DB::shouldReceive('beginTransaction');
        DB::shouldReceive('rollBack');
        
        $this->transactionService->deleteTransaction($id);
    }

    public function test_delete_transaction_should_forget_cache_and_increment_stock_when_type_is_out()
    {
        Cache::spy();

        $id = 1;
        $qty = 10;
        $item_id = 1;
        $current_stock = 10;

        Cache::shouldReceive('remember')
            ->once()
            ->with("transaction_$id", 3600, \Closure::class)
            ->andReturnUsing(function ($key, $ttl, $closure) {
                return $closure();
            });

        DB::shouldReceive('beginTransaction');
        DB::shouldReceive('commit');

        $this->transactionRepository
            ->shouldReceive('find')
            ->once()
            ->with($id)
            ->andReturn(new Transaction([
                'item_id' => $item_id,
                'user_id' => 1,
                'type' => "out",
                'quantity' => $qty
            ]));

        $this->itemRepository
            ->shouldReceive('getStock')
            ->once()
            ->with($item_id)
            ->andReturn($current_stock);

        $this->itemRepository
            ->shouldReceive('updateStock')
            ->once()
            ->with($item_id, $current_stock + $qty)
            ->andReturn(true);

        $this->transactionRepository
            ->shouldReceive('delete')
            ->once()
            ->with($id)
            ->andReturn(true);

        $result = $this->transactionService->deleteTransaction($id);

        Cache::shouldHaveReceived('forget')
            ->with("item_$item_id");

        Cache::shouldHaveReceived('forget')
            ->with("transaction_$id");

        Cache::shouldHaveReceived('forget')
            ->twice();

        $this->assertEquals(true, $result);
    }

    public function test_delete_transaction_throws_InsufficientStockException_when_type_is_in()
    {
        $this->expectException(InsufficientStockException::class);
        $this->expectExceptionCode(422);

        $id = 1;
        $qty = 10;
        $item_id = 1;
        $current_stock = 5;

        Cache::shouldReceive('remember')
            ->once()
            ->with("transaction_$id", 3600, \Closure::class)
            ->andReturnUsing(function ($key, $ttl, $closure) {
                return $closure();
            });

        DB::shouldReceive('beginTransaction');
        DB::shouldReceive('rollBack');

        $this->transactionRepository
            ->shouldReceive('find')
            ->once()
            ->with($id)
            ->andReturn(new Transaction([
                'item_id' => $item_id,
                'user_id' => 1,
                'type' => "in",
                'quantity' => $qty
            ]));

        $this->itemRepository
            ->shouldReceive('getStock')
            ->once()
            ->with($item_id)
            ->andReturn($current_stock);

        $this->transactionService->deleteTransaction($id);

    }

    public function test_delete_transaction_should_forget_cache_and_increment_stock_when_type_is_in()
    {
        Cache::spy();

        $id = 1;
        $qty = 10;
        $item_id = 1;
        $current_stock = 10;

        Cache::shouldReceive('remember')
            ->once()
            ->with("transaction_$id", 3600, \Closure::class)
            ->andReturnUsing(function ($key, $ttl, $closure) {
                return $closure();
            });

        DB::shouldReceive('beginTransaction');
        DB::shouldReceive('commit');

        $this->transactionRepository
            ->shouldReceive('find')
            ->once()
            ->with($id)
            ->andReturn(new Transaction([
                'item_id' => $item_id,
                'user_id' => 1,
                'type' => "in",
                'quantity' => $qty
            ]));

        $this->itemRepository
            ->shouldReceive('getStock')
            ->once()
            ->with($item_id)
            ->andReturn($current_stock);


        $this->itemRepository
            ->shouldReceive('updateStock')
            ->once()
            ->with($item_id, $current_stock - $qty)
            ->andReturn(true);

        $this->transactionRepository
            ->shouldReceive('delete')
            ->once()
            ->with($id)
            ->andReturn(true);

        $result = $this->transactionService->deleteTransaction($id);

        Cache::shouldHaveReceived('forget')
            ->with("item_$item_id");

        Cache::shouldHaveReceived('forget')
            ->with("transaction_$id");

        Cache::shouldHaveReceived('forget')
            ->twice();

        $this->assertEquals(true, $result);
    }

    public function test_paginate_transaction_and_return_LengthAwarePaginator()
    {
        $transactions = new Collection([
            new Transaction(),
            new Transaction(),
            new Transaction(),
        ]);

        $paginator = new LengthAwarePaginator(
            $transactions,
            $transactions->count(),
            10
        );

        $this->transactionRepository
            ->shouldReceive('paginate')
            ->once()
            ->with(10)
            ->andReturn($paginator);

        $result = $this->transactionService->paginateTransaction(10);


        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }
}