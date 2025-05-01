<?php

namespace Tests\Feature\Services;

use App\DTOs\CreateItemDTO;
use App\DTOs\ItemDTO;
use App\DTOs\UpdateItemDTO;
use App\Exceptions\InsufficientStockException;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ItemServiceTest extends TestCase
{
    protected $itemService;

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var \App\Services\ItemService */
        $this->itemService = $this->app->make(\App\Services\ItemService::class);

        Cache::flush();
    }

    public function test_paginate_item_throws_ModelNotFoundException_with_invalid_category_id()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Category Not Found.');
        $this->expectExceptionCode(404);

        $this->itemService->paginateItems(999);
    }

    public function test_paginate_item_return_LengthAwarePaginator_with_valid_category_id()
    {
        $category = Category::create([
            'name' => 'Test category',
            'description' => null
        ]);

        $category->items()
            ->insert(
                [
                    [
                        'category_id' => $category->id,
                        'name' => 'Pensil',
                        'code' => '#23ACD',
                        'stock' => 20
                    ],
                    [
                        'category_id' => $category->id,
                        'name' => 'Laptop',
                        'code' => '#23ACD',
                        'stock' => 100
                    ],
                    [
                        'category_id' => $category->id,
                        'name' => 'Pulpen',
                        'code' => '#23ACD',
                        'stock' => 120
                    ]
                ]
            );

        $result = $this->itemService->paginateItems($category->id);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(3, $result->count());
    }

    public function test_find_item_throws_ModelNotFoundException_with_invalid_id()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage("Item Not Found.");
        $this->expectExceptionCode(404);

        $this->itemService->findItem(999);
    }

    public function test_find_item_with_valid_id_should_cache_and_return_Item()
    {
        $category = Category::create(['name' => 'Elektronik', 'description' => null]);
        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'Sendal',
            'code' => '#SDL20',
            'stock' => 20
        ]);

        $result = $this->itemService->findItem($item->id);

        $this->assertInstanceOf(Item::class, $result);
        $this->assertEquals('Sendal', $item->name);
    }

    public function test_create_item_throws_ModelNotFoundException_with_invalid_category_id()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Category Not Found.');
        $this->expectExceptionCode(404);

        $dto = new CreateItemDTO(
            'Sarung',
            '#SRG500',
            500
        );

        $this->itemService->createItem(999, $dto);

        $this->assertDatabaseMissing('items', [
            'name' => 'Sarung',
            'code' => '#SRG500',
            'stock' => 500
        ]);
    }

    public function test_create_item_with_valid_category_id_and_return_Item()
    {
        $category = Category::create(['name' => 'Cloth', 'description' => null]);
        $dto = new CreateItemDTO(
            'Kemeja',
            '#KMJ500',
            500
        );

        $result = $this->itemService->createItem($category->id, $dto);

        $this->assertInstanceOf(Item::class, $result);
        $this->assertDatabaseHas('items', [
            'name' => 'Kemeja',
            'code' => '#KMJ500',
            'stock' => 500
        ]);
    }

    public function test_update_item_throws_ModelNotFoundException_with_invalid_id()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Item Not Found.');
        $this->expectExceptionCode(404);

        $this->itemService->findItem(999);
    }

    public function test_update_item_should_forget_cache_and_return_Item()
    {
        $category = Category::create(['name' => 'Cloth', 'description' => null]);
        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'Mouse',
            'code' => '#MUE300',
            'stock' => 30
        ]);
        $dto = new UpdateItemDTO(
            'Keyboard',
            10
        );

        Cache::put("item_$item->id", $item, 3600);
        $result = $this->itemService->updateItem($item->id, $dto);

        $this->assertEquals(null, Cache::get("item_$item->id"));
        $this->assertInstanceOf(Item::class, $result);
        $this->assertDatabaseHas('items', [
            'name' => 'Keyboard',
            'stock' => 10
        ]);
    }

    public function test_delete_item_throws_ModelNotFoundException_with_invalid_id()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Item Not Found.');
        $this->expectExceptionCode(404);

        $this->itemService->deleteItem(999);
    }

    public function test_delete_item_with_valid_id_should_forget_cache_and_return_true()
    {
        $category = Category::create(['name' => 'Cloth', 'description' => null]);
        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'Monitor',
            'code' => '#MNR300',
            'stock' => 30
        ]);

        Cache::put("item_$item->id", $item, 3600);
        $result = $this->itemService->deleteItem($item->id);

        $this->assertTrue($result);
        $this->assertEquals(null, Cache::get("item_$item->id"));
        $this->assertDatabaseMissing('items', [
            'name' => 'Monitor',
            'code' => '#MNR300',
            'stock' => 30
        ]);
    }

    public function test_get_stock_item_throws_ModelNotFoundException_with_invalid_id()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Item Not Found.');
        $this->expectExceptionCode(404);

        $this->itemService->getStockItem(999);
    }

    public function test_get_stock_item_with_valid_id_and_return_integer()
    {
        $category = Category::create(['name' => 'Cloth', 'description' => null]);
        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'TEST',
            'code' => '#TS300',
            'stock' => 1000
        ]);

        $result = $this->itemService->getStockItem($item->id);

        $this->assertEquals(1000, $result);
    }

    public function test_decrement_stock_item_throws_InsufficientStockException_when_decrement_stock()
    {
        $this->expectException(InsufficientStockException::class);
        $this->expectExceptionCode(422);

        $category = Category::create(['name' => 'Cloth', 'description' => null]);
        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'TEST_DECREMENT',
            'code' => '#TSDT300',
            'stock' => 10
        ]);

        $this->itemService->decrementStockItem($item->id, 15);
        $this->assertDatabaseHas('items', [
            'name' => 'TEST_DECREMENT',
            'code' => '#TSDT300',
            'stock' => 10
        ]);
    }

    public function test_decrement_stock_item_with_valid_data_should_forget_cache_and_return_true()
    {
        $category = Category::create(['name' => 'Cloth', 'description' => null]);
        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'TEST_DECREMENT_2',
            'code' => '#TSDT2300',
            'stock' => 50
        ]);

        Cache::put("item_$item->id", $item, 3600);
        $result = $this->itemService->decrementStockItem($item->id, 10);

        $this->assertTrue($result);
        $this->assertEquals(null, Cache::get("item_$item->id"));
        $this->assertDatabaseHas('items', [
            'name' => 'TEST_DECREMENT_2',
            'code' => '#TSDT2300',
            'stock' => 40
        ]);
    }

    public function test_increment_stock_item_with_valid_data_should_forget_cache_and_return_true()
    {
        $category = Category::create(['name' => 'Cloth', 'description' => null]);
        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'TEST_INCREMENT',
            'code' => '#TSDT2300',
            'stock' => 50
        ]);

        Cache::put("item_$item->id", $item, 3600);
        $result = $this->itemService->incrementStockItem($item->id, 10);

        $this->assertTrue($result);
        $this->assertEquals(null, Cache::get("item_$item->id"));
        $this->assertDatabaseHas('items', [
            'name' => 'TEST_INCREMENT',
            'code' => '#TSDT2300',
            'stock' => 60
        ]);
    }
}
