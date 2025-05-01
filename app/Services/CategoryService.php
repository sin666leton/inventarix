<?php
namespace App\Services;

use App\Contracts\Category;
use App\DTOs\CategoryDTO;
use App\Jobs\LogItemChangeJob;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CategoryService
{
    public function __construct(
        protected Category $categoryRepository,
        protected LogManager $logger
    ) {}

    public function paginateCategory(int $each = 10)
    {
        return $this->categoryRepository->paginate($each);
    }

    public function getAllCategories()
    {
        return Cache::remember('categories_all', 3600, fn() => $this->categoryRepository->all());
    }

    public function findCategory(int $id)
    {
        return Cache::remember("category_$id", 3600, fn() => $this->categoryRepository->find($id));
    }

    public function createCategory(CategoryDTO $dto)
    {
        $category = $this->categoryRepository->create($dto);
    
        Cache::forget('categories_all');
        $this->logger->channel('model')->info('Create category.', [
            'id' => $category->id,
            'name' => $category->name
        ]);

        return $category;
    }

    public function updateCategory(int $id, CategoryDTO $dto)
    {
        $category = $this->categoryRepository->update($id, $dto);
    
        Cache::forget('categories_all');
        Cache::forget("category_$id");
        $this->logger->channel('model')->info('Update category.', [
            'id' => $category->id,
            'name' => $category->name
        ]);

        return $category;
    }

    public function deleteCategory(int $id)
    {
        $result = $this->categoryRepository->delete($id);
    
        if ($result) {
            Cache::forget("categories_all");
            $this->logger->channel('model')->info('Delete category.', [
                'id' => $id
            ]);
        }

        return $result;
    }
}