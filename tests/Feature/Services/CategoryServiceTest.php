<?php

namespace Tests\Feature\Services;

use App\Models\Category;
use App\DTOs\CategoryDTO;
use App\Services\CategoryService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CategoryServiceTest extends TestCase
{
    protected $categoryService;

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var CategoryService */
        $this->categoryService = $this->app->make(CategoryService::class);

        Cache::flush();
    }

    public function test_paginate_category_return_LengthAwarePaginator()
    {
        $each = 10;
        $result = $this->categoryService->paginateCategory($each);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals($each, $result->perPage());
    }

    public function test_get_all_category_return_collection()
    {
        Category::insert([
            [
                'name' => 'testing',
                'description' => null
            ],
            [
                'name' => 'tester',
                'description' => null
            ],
            [
                'name' => 'shiuu',
                'description' => '123'
            ]
        ]);

        $result = $this->categoryService->getAllCategories();

        $this->assertCount(3, $result);
        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_create_category_with_valid_data_will_forget_cache_and_return_Category()
    {
        Cache::remember('categories_all', 3600, fn() => collect());

        $expectedData = [
            "name" => "Elektronik_create",
            "description" => "Deskripsi elektronik_create"
        ];

        $dto = new CategoryDTO($expectedData['name'], $expectedData['description']);

        $result = $this->categoryService->createCategory($dto);

        $this->assertInstanceOf(Category::class, $result);
        $this->assertEquals(null, Cache::get('categories_all'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Elektronik_create',
            "description" => "Deskripsi elektronik_create"
        ]);
    }

    public function test_find_category_throws_ModelNotFoundException_with_invalid_id()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Category Not Found.');
        $this->expectExceptionCode(404);

        $id = 999;

        $this->categoryService->findCategory($id);
    }

    public function test_find_category_with_valid_id_return_Category()
    {
        $category = Category::create([
            'name' => 'Elektronik',
            'description' => null
        ]);

        $result = $this->categoryService->findCategory($category->id);

        $this->assertInstanceOf(Category::class, $result);
        $this->assertInstanceOf(Category::class, Cache::get("category_$category->id"));
    }

    public function test_update_category_throws_ModelNotFoundException_when_category_not_found()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Category Not Found.');
        $this->expectExceptionCode(404);

        $this->categoryService->updateCategory(999, new CategoryDTO('Roooood', null));
    }

    public function test_update_category_with_valid_data_will_forget_cache_and_return_category()
    {
        $category = Category::create([
            'name' => 'Elektronik',
            'description' => null
        ]);

        $dto = new CategoryDTO('Elek', 'test');

        $result = $this->categoryService->updateCategory($category->id, $dto);

        $this->assertInstanceOf(Category::class, $result);
        $this->assertEquals($dto->name, $result->name);
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Elek',
            'description' => 'test'
        ]);
    }

    public function test_delete_category_throws_ModelNotFoundException_when_category_not_found()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Category Not Found.');
        $this->expectExceptionCode(404);

        $id = 999;

        $this->categoryService->deleteCategory($id);
    }

    public function test_delete_category_when_category_valid_return_boolean()
    {
        $category = Category::create([
            'name' => 'Elektronik',
            'description' => null
        ]);

        $result = $this->categoryService->deleteCategory($category->id);

        $this->assertEquals(true, $result);
        $this->assertDatabaseMissing('categories', [
            'name' => 'Elektronik',
            'description' => null
        ]);
    }
}
