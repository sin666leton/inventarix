<?php

namespace Tests\Unit\Service;

use App\DTOs\CreateItemDTO;
use App\DTOs\ItemDTO;
use App\DTOs\UpdateItemDTO;
use App\Exceptions\InsufficientStockException;
use App\Models\Item;
use App\Services\ItemService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Log\LogManager;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Mockery;
use PHPUnit\Framework\TestCase;

class ItemServiceTest extends TestCase
{
    private $itemRepository;

    private $categoryRepository;

    private $itemService;

    private $logManager;

    protected function setUp(): void
    {
        parent::setUp();

        /** 
         * @var \App\Contracts\Item&Mockery\MockInterface
         **/
        $this->itemRepository = Mockery::mock(\App\Contracts\Item::class);
        
        /** @var LogManager&Mockery\MockInterface */
        $this->logManager = Mockery::mock(LogManager::class);

        /** 
         * @var \App\Contracts\Category&Mockery\MockInterface
         **/
        $this->categoryRepository = Mockery::mock(\App\Contracts\Category::class);

        $this->itemService = new ItemService($this->itemRepository, $this->categoryRepository, $this->logManager);
    }

    protected function tearDown(): void
    {
        Cache::swap(new \Illuminate\Cache\Repository(new \Illuminate\Cache\ArrayStore()));
        Mockery::close();

        parent::tearDown();
        
    }

    public function test_paginate_item_throws_ModelNotFoundException_with_invalid_category_id()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage("Category Not Found.");

        $this->categoryRepository
            ->shouldReceive('exists')
            ->once()
            ->with(999)
            ->andReturn(false);

        $this->itemService->paginateItems(category_id: 999, perPage: 10);
    }

    public function test_paginate_item_return_LengthAwarePaginator_with_valid_category_id()
    {
        $category_id = 1;
        $perPage = 10;
        
        $items = new Collection([
            new Item([
                'category_id' => $category_id,
                'name' => 'Pulpen',
                'code' => Str::random(5),
                'stock' => 5
            ]),
            new Item([
                'category_id' => $category_id,
                'name' => 'Laptop',
                'code' => Str::random(5),
                'stock' => 10
            ])
        ]);

        $paginator = new LengthAwarePaginator(
            $items,
            $items->count(),
            $perPage,
            1
        );

        $this->categoryRepository
            ->shouldReceive('exists')
            ->once()
            ->with($category_id)
            ->andReturn(true);

        $this->itemRepository
            ->shouldReceive('paginate')
            ->once()
            ->with($category_id, $perPage)
            ->andReturn($paginator);

        $result = $this->itemService->paginateItems(category_id: 1, perPage: 10);

        $this->assertEquals($result->total(), $items->count());
    }

    public function test_find_item_throws_ModelNotFoundException_with_invalid_id()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage("Item Not Found.");
        $this->expectExceptionCode(404);

        $id = 999;

        $this->itemRepository
            ->shouldReceive('find')
            ->once()
            ->with($id)
            ->andThrowExceptions([
                new ModelNotFoundException('Item Not Found.', 404)
            ]);

        $this->itemService->findItem($id);
    }

    public function test_find_item_with_valid_id_should_cache_and_return_Item()
    {
        $id = 1;

        $this->itemRepository
            ->shouldReceive('find')
            ->once()
            ->with($id)
            ->andReturn(new Item([
                'name' => 'Pulpen',
                'code' => '#ABR421',
                'stock' => 5
            ]));

        Cache::shouldReceive('remember')
            ->once()
            ->with("item_$id", 3600, \Closure::class)
            ->andReturnUsing(function ($key, $ttl, $closure) {
                return $closure();
            });

        $result = $this->itemService->findItem($id);


        $this->assertInstanceOf(Item::class, $result);
        $this->assertEquals('#ABR421', $result->code);
    }

    public function test_create_item_throws_ModelNotFoundException_with_invalid_category_id()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage("Category Not Found.");
        $this->expectExceptionCode(404);

        $category_id = 999;

        $dto = new CreateItemDTO(
            'Pulpen',
            '#AAB3D',
            5
        );

        $this->categoryRepository
            ->shouldReceive('exists')
            ->once()
            ->with($category_id)
            ->andReturn(false);

        $this->itemService->createItem($category_id, $dto);
    }

    public function test_create_item_with_valid_category_id_and_return_Item()
    {
        $category_id = 1;

        $dto = new CreateItemDTO(
            'Pulpen',
            '#AAB3D',
            5
        );

        $this->logManager
            ->shouldReceive('channel')
            ->once()
            ->with('model')
            ->andReturnSelf();

        $this->logManager
            ->shouldReceive('info')
            ->once();

        $this->categoryRepository
            ->shouldReceive('exists')
            ->once()
            ->with($category_id)
            ->andReturn(true);

        $this->itemRepository
            ->shouldReceive('create')
            ->once()
            ->with($category_id, $dto)
            ->andReturn(new Item($dto->toArray()));

        $result = $this->itemService->createItem($category_id, $dto);

        $this->assertInstanceOf(Item::class, $result);
        $this->assertEquals($result->code, $dto->code);
    }

    public function test_update_item_throws_ModelNotFoundException_with_invalid_id()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage("Item Not Found.");
        $this->expectExceptionCode(404);

        $id = 999;
        $dto = new UpdateItemDTO(
            'test',
            5
        );

        $this->itemRepository
            ->shouldReceive('update')
            ->once()
            ->with($id, $dto)
            ->andThrowExceptions([
                new ModelNotFoundException('Item Not Found.', 404)
            ]);

        $this->itemService->updateItem($id, $dto);
    }

    public function test_update_item_should_forget_cache_and_return_Item()
    {
        Cache::spy();

        $id = 1;
        $dto = new UpdateItemDTO(
            'Laptop',
            1
        );

        $this->itemRepository
            ->shouldReceive('update')
            ->once()
            ->with($id, $dto)
            ->andReturn(new Item($dto->toArray()));

        $this->logManager
            ->shouldReceive('channel')
            ->once()
            ->with('model')
            ->andReturnSelf();

        $this->logManager
            ->shouldReceive('info')
            ->once();

        $result = $this->itemService->updateItem($id, $dto);

        Cache::shouldHaveReceived('forget')
            ->once()
            ->with("item_$id");

        $this->assertInstanceOf(Item::class, $result);
    }

    public function test_delete_item_throws_ModelNotFoundException_with_invalid_id()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage("Item Not Found.");
        $this->expectExceptionCode(404);

        $id = 999;

        $this->itemRepository
            ->shouldReceive('delete')
            ->once()
            ->with($id)
            ->andThrowExceptions([
                new ModelNotFoundException('Item Not Found.', 404)
            ]);

        $this->itemService->deleteItem($id);
    }

    public function test_delete_item_with_valid_id_should_forget_cache_and_return_true()
    {
        Cache::spy();

        $id = 1;

        $this->itemRepository
            ->shouldReceive('delete')
            ->once()
            ->with($id)
            ->andReturn(true);

        $this->logManager
            ->shouldReceive('channel')
            ->once()
            ->with('model')
            ->andReturnSelf();

        $this->logManager
            ->shouldReceive('info')
            ->once();

        $result = $this->itemService->deleteItem($id);

        Cache::shouldHaveReceived('forget')
            ->once()
            ->with("item_$id");

        $this->assertEquals(true, $result);
    }

    public function test_get_stock_item_throws_ModelNotFoundException_with_invalid_id()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Item Not Found.');
        $this->expectExceptionCode(404);

        $id = 999;

        $this->itemRepository
            ->shouldReceive('getStock')
            ->once()
            ->with($id)
            ->andThrowExceptions([
                new ModelNotFoundException('Item Not Found.', 404)
            ]);

        $this->itemService->getStockItem($id);

    }

    public function test_get_stock_item_with_valid_id_and_return_integer()
    {
        $id = 1;

        $this->itemRepository
            ->shouldReceive('getStock')
            ->once()
            ->with($id)
            ->andReturn(10);

        $result = $this->itemService->getStockItem($id);

        $this->assertEquals(10, $result);
    }

    public function test_decrement_stock_item_throws_InsufficientStockException_when_decrement_stock()
    {
        $this->expectException(InsufficientStockException::class);
        $this->expectExceptionCode(422);

        $id = 1;
        $amount = 20;

        $this->itemRepository
            ->shouldReceive('getStock')
            ->once()
            ->with($id)
            ->andReturn(10);

        $this->itemService->decrementStockItem($id, $amount);
    }

    public function test_decrement_stock_item_with_valid_data_should_forget_cache_and_return_true()
    {
        Cache::spy();

        $id = 1;
        $amount = 5;

        $this->itemRepository
            ->shouldReceive('getStock')
            ->once()
            ->with($id)
            ->andReturn(10);
        
        $this->itemRepository
            ->shouldReceive('updateStock')
            ->once()
            ->with($id, 10 - $amount)
            ->andReturn(true);

        $this->logManager
            ->shouldReceive('channel')
            ->once()
            ->with('stocks')
            ->andReturnSelf();

        $this->logManager
            ->shouldReceive('info')
            ->once();

        $result = $this->itemService->decrementStockItem($id, $amount);

        Cache::shouldHaveReceived('forget')
            ->once()
            ->with("item_$id");

        $this->assertEquals(true, $result);
    }

    public function test_increment_stock_item_with_valid_data_should_forget_cache_and_return_true()
    {
        Cache::spy();

        $id = 1;
        $amount = 5;

        $this->itemRepository
            ->shouldReceive('getStock')
            ->once()
            ->with($id)
            ->andReturn(10);
        
        $this->itemRepository
            ->shouldReceive('updateStock')
            ->once()
            ->with($id, $amount + 10)
            ->andReturn(true);

        $this->logManager
            ->shouldReceive('channel')
            ->once()
            ->with('stocks')
            ->andReturnSelf();

        $this->logManager
            ->shouldReceive('info')
            ->once();

        $result = $this->itemService->incrementStockItem($id, $amount);
        
        Cache::shouldHaveReceived('forget')
            ->once()
            ->with("item_$id");
        
        $this->assertEquals(true, $result);
    }
}
