<?php

namespace Tests\Unit\Service;

use App\DTOs\TransactionDTO;
use App\Exceptions\InsufficientStockException;
use App\Models\Category;
use App\Models\Item;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
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

        $role = new Role([
            'id' => 1,
            'name' => 'admin'
        ]);

        $user = new User([
            'id' => 4,
            'role_id' => $role->id,
            'name' => 'balmond',
            'email' => 'balmond@example.com'
        ]);

        $user->setRelation('role', $role);

        $this->transactionRepository
            ->shouldReceive('find')
            ->once()
            ->with($id)
            ->andThrowExceptions([
                new ModelNotFoundException('Transaction Not Found.', 404)
            ]);

        $this->transactionService->findTransaction($user, $id);
    }

    public function test_find_transaction_return_Transaction_with_valid_id()
    {
        $id = 1;

        $role = new Role([
            'id' => 1,
            'name' => 'admin'
        ]);

        $user = new User([
            'id' => 4,
            'role_id' => $role->id,
            'name' => 'balmond',
            'email' => 'balmond@example.com'
        ]);

        $user->setRelation('role', $role);

        $this->transactionRepository
            ->shouldReceive('find')
            ->once()
            ->with($id)
            ->andReturn(new Transaction());

        $result = $this->transactionService->findTransaction($user, $id);
    
        $this->assertInstanceOf(Transaction::class, $result);
    }

    public function test_find_transaction_when_user_is_staff_and_return_transaction_with_valid_id()
    {
        $id = 1;

        $role = new Role([
            'id' => 2,
            'name' => 'staff'
        ]);

        $staff = (new User())->forceFill([
            'id' => 1,
            'role_id' => $role->id,
            'name' => 'balmond',
            'email' => 'balmond@example.com'
        ]);

        $staff->setRelation('role', $role);

        $this->transactionRepository
            ->shouldReceive('findStaffTransaction')
            ->once()
            ->with($staff->id, $id)
            ->andReturn(new Transaction());
        
        $result = $this->transactionService->findTransaction($staff, $id);
    
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

    public function test_create_transaction_should_forget_cache_with_valid_data_and_return_Transaction_when_transaction_type_is_out()
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

    public function test_create_transaction_should_forget_cache_with_valid_data_and_return_Transaction_when_transaction_type_is_in()
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
            ->once()
            ->with("item_$item_id");

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
            ->once()
            ->with("item_$item_id");

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

    public function test_findTransactionWithUserAndItem_throws_ModelNotFoundException_with_invalid_id()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Transaction Not Found.');
        $this->expectExceptionCode(404);

        $id = 999;

        $role = new Role(['name' => 'admin']);
        $user = new User([
            'role_id' => $role->id,
            'name' => 'admin'
        ]);

        $user->setRelation('role', $role);

        $this->transactionRepository
            ->shouldReceive('findWithUserAndItem')
            ->once()
            ->with($id)
            ->andThrow(new ModelNotFoundException("Transaction Not Found.", 404));

        $this->transactionService->findTransactionWithUserAndItem($user, $id);
    }

    public function test_findTransactionWithUserAndItem_as_admin_and_return_Transaction()
    {
        $id = 1;
        $role = new Role(['name' => 'admin']);

        $user = new User([
            'id' => 2,
            'role_id' => $role->id,
            'name' => 'admin',
            'email' => 'admin@example.com',
        ]);

        $user->setRelation('role', $role);

        $item = new Item([
            'id' => 3,
            'name' => 'laptop',
            'code' => '#LPT',
            'stock' => 20
        ]);

        $transaction = new Transaction([
            'id' => $id,
            'user_id' => $user->id,
            'item_id' => $item->id,
            'type' => 'in',
            'quantity' => 4,
            'description' => null
        ]);

        $transaction->setRelations([
            'user' => $user,
            'item' => $item
        ]);

        $this->transactionRepository
            ->shouldReceive('findWithUserAndItem')
            ->once()
            ->with($id)
            ->andReturn($transaction);

        $result = $this->transactionService->findTransactionWithUserAndItem($user, $id);

        $this->assertInstanceOf(Transaction::class, $result);
        $this->assertEquals('admin', $result->user->name);
        $this->assertEquals('laptop', $result->item->name);
    }

    public function test_findTransactionWithUserAndItem_as_staff_and_return_Transaction()
    {
        $id = 1;
        $role = new Role(['name' => 'staff']);

        $user = (new User())->forceFill([
            'id' => 2,
            'role_id' => $role->id,
            'name' => 'staff',
            'email' => 'staff@example.com',
        ]);

        $user->setRelation('role', $role);

        $item = new Item([
            'id' => 3,
            'name' => 'laptop',
            'code' => '#LPT',
            'stock' => 20
        ]);

        $transaction = new Transaction([
            'id' => $id,
            'user_id' => $user->id,
            'item_id' => $item->id,
            'type' => 'in',
            'quantity' => 4,
            'description' => null
        ]);

        $transaction->setRelations([
            'user' => $user,
            'item' => $item
        ]);

        $this->transactionRepository
            ->shouldReceive('findStaffTransaction')
            ->once()
            ->with($user->id, $id)
            ->andReturn($transaction);

        $result = $this->transactionService->findTransactionWithUserAndItem($user, $id);

        $this->assertInstanceOf(Transaction::class, $result);
    }
}