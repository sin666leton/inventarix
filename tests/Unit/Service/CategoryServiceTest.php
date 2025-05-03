<?php
declare(strict_types=1);
namespace Tests\Unit\Service;

use App\DTOs\CategoryDTO;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Log\LogManager;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Mockery;
use PHPUnit\Framework\TestCase;
use Illuminate\Database\Eloquent\Collection;

class CategoryServiceTest extends TestCase
{
    protected $categoryRepository;

    protected $categoryService;

    protected $logManagerMock;

    protected function setUp(): void
    {
        parent::setUp();
        /** 
         * @var \App\Contracts\Category&Mockery\MockInterface
         **/
        $this->categoryRepository = Mockery::mock(\App\Contracts\Category::class);

        /** @var LogManager&Mockery\MockInterface */
        $this->logManagerMock = Mockery::mock(LogManager::class);

        $this->categoryService = new CategoryService($this->categoryRepository, $this->logManagerMock);
    }

    protected function tearDown(): void
    {
        Cache::swap(new \Illuminate\Cache\Repository(new \Illuminate\Cache\ArrayStore()));
        Mockery::close();

        parent::tearDown();
        
    }

    public function test_paginate_category_return_LengthAwarePaginator()
    {
        $each = 10;
        $items = collect([
            new Category(['id' => 1, 'name' => 'test']),
            new Category(['id' => 2, 'name' => 'elektronik']),
            new Category(['id' => 3, 'name' => 'baju']),
        ]);

        $expectedData = new LengthAwarePaginator($items, $items->count(), $each, 1);

        $this->categoryRepository
            ->shouldReceive('paginate')
            ->once()
            ->with($each)
            ->andReturn($expectedData);

        $result = $this->categoryService->paginateCategory($each);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertSame($expectedData, $result);
    }

    public function test_get_all_category_return_collection()
    {
        $expectedData = new Collection([
            new Category([
                'name' => 'Elektronik',
                'description' => 'Deskripsi elektronik'
            ]),
            new Category([
                'name' => 'Bahan Mentah',
                'description' => 'Bahan mentah untuk kebutuhan konsumsi'
            ]),
        ]);

        Cache::shouldReceive('remember')
            ->once()
            ->with('categories_all', 3600, \Closure::class)
            ->andReturnUsing(function ($key, $ttl, $closure) {
                return $closure();
            });

        $this->categoryRepository
            ->shouldReceive('all')
            ->once()
            ->andReturn($expectedData);

        $result = $this->categoryService->getAllCategories();
            
        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_create_category_throws_TypeError_when_data_invalid()
    {
        $this->expectException(\TypeError::class);

        $expectedData = [
            "name" => null,
            "description" => "Deskripsi elektronik"
        ];

        new CategoryDTO($expectedData['name'], $expectedData['description']);
    }

    public function test_create_category_with_valid_data_will_forget_cache_and_return_Category()
    {
        Cache::spy();

        $expectedData = [
            "name" => "Elektronik",
            "description" => "Deskripsi elektronik"
        ];

        $dto = new CategoryDTO($expectedData['name'], $expectedData['description']);

        $this->categoryRepository->shouldReceive('create')
            ->once()
            ->with($dto)
            ->andReturn(new Category($dto->toArray()));

        $this->logManagerMock
            ->shouldReceive('channel')
            ->once()
            ->with('model')
            ->andReturnSelf();

        $this->logManagerMock
            ->shouldReceive('info')
            ->once();

        $result = $this->categoryService->createCategory($dto);

        Cache::shouldHaveReceived('forget')
            ->once()
            ->with('categories_all');

        $this->assertEquals($expectedData, $result->toArray());
    }

    public function test_find_category_throws_ModelNotFoundException_with_invalid_id()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Category Not Found.');
        $this->expectExceptionCode(404);

        $id = 999;

        $this->categoryRepository
            ->shouldReceive('find')
            ->once()
            ->with($id)
            ->andThrowExceptions([
                new ModelNotFoundException("Category Not Found.", 404)
            ]);

        $this->categoryService->findCategory($id);
    }

    public function test_find_category_throws_TypeError_when_id_invalid()
    {
        $this->expectException(\TypeError::class);

        $this->categoryService->findCategory("1");
    }

    public function test_find_category_with_valid_id_return_Category()
    {
        $id = 1;
        $expectedData = new Category([
            'name' => 'Elektronik',
            'description' => 'Barang elektronik'
        ]);

        $this->categoryRepository
            ->shouldReceive('find')
            ->once()
            ->with($id)
            ->andReturn($expectedData);

        Cache::shouldReceive('remember')
            ->once()
            ->with("category_$id", 3600, \Closure::class)
            ->andReturnUsing(function ($key, $ttl, $closure) {
                return $closure();
            });

        $result = $this->categoryService->findCategory(intval($id));
    
        $this->assertEquals($expectedData->name, $result->name);
    }

    public function test_update_category_throws_ModelNotFoundException_when_category_not_found()
    {
        $this->expectException(ModelNotFoundException::class);

        $id = 999;
        $dto = new CategoryDTO('Test', null);

        $this->categoryRepository
            ->shouldReceive('update')
            ->once()
            ->with($id, $dto)
            ->andThrowExceptions([
                new ModelNotFoundException('Category Not Found.', 404)
            ]);
    
        $this->categoryService->updateCategory($id, $dto);
    }

    public function test_update_category_with_valid_data_will_forget_cache_and_return_category()
    {
        Cache::spy();

        $id = 1;
        $dto = new CategoryDTO('Test', null);
        $expectedData = new Category($dto->toArray());

        $this->categoryRepository
            ->shouldReceive('update')
            ->once()
            ->with($id, $dto)
            ->andReturn($expectedData);

        $this->logManagerMock
            ->shouldReceive('channel')
            ->once()
            ->with('model')
            ->andReturnSelf();

        $this->logManagerMock
            ->shouldReceive('info')
            ->once();
            
        $result = $this->categoryService->updateCategory($id, $dto);

        Cache::shouldHaveReceived('forget')
            ->with('categories_all');

        Cache::shouldHaveReceived('forget')
            ->with("category_$id");

        Cache::shouldHaveReceived('forget')->twice();

        $this->assertEquals($expectedData->toArray(), $result->toArray());
    }

    public function test_delete_category_throws_ModelNotFoundException_when_category_not_found()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Category Not Found.');
        $this->expectExceptionCode(404);

        $id = 999;

        $this->categoryRepository
            ->shouldReceive('delete')
            ->with($id)
            ->andThrowExceptions([
                new ModelNotFoundException('Category Not Found.', 404)
            ]);

        $this->categoryService->deleteCategory($id);
    }

    public function test_delete_category_throws_TypeError_when_id_not_integer()
    {
        $this->expectException(\TypeError::class);

        $id = "testing";

        $this->categoryService->deleteCategory($id);
    }

    public function test_delete_category_when_category_valid_return_boolean()
    {
        Cache::spy();

        $id = 1;

        $this->categoryRepository
            ->shouldReceive('delete')
            ->with($id)
            ->andReturn(true);

        $this->logManagerMock
            ->shouldReceive('channel')
            ->once()
            ->with('model')
            ->andReturnSelf();

        $this->logManagerMock
            ->shouldReceive('info')
            ->once();
            
        $result = $this->categoryService->deleteCategory($id);
    
        Cache::shouldHaveReceived('forget')
            ->once()
            ->with('categories_all');

        $this->assertEquals(true, $result);
    }
}
