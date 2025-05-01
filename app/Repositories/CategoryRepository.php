<?php

namespace App\Repositories;

use App\Exceptions\CategoryNotFoundException;
use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CategoryRepository implements \App\Contracts\Category
{
    public function paginate(int $item = 10): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Category::paginate($item);
    }

    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return Category::select(['id', 'name'])->get();
    }

    public function find(int $id): Category
    {
        $category = Category::select(['id', 'name', 'description'])
            ->where('id', $id)
            ->firstOr(function () {
                throw new CategoryNotFoundException();
            });

        return $category;
    }

    public function exists(int $id): bool
    {
        return Category::where('id', $id)->exists();
    }

    public function create(\App\DTOs\CategoryDTO $dto): Category
    {
        $category = Category::create($dto->toArray());

        return $category;
    }

    public function update(int $id, \App\DTOs\CategoryDTO $dto): Category
    {
        $category = Category::where('id', $id)->firstOr(function () {
            throw new CategoryNotFoundException();
        });
        $category->update($dto->toArray());

        return $category;
    }

    public function delete(int $id): bool
    {
        $category = Category::where('id', $id)->firstOr(function () {
            throw new CategoryNotFoundException();
        });

        return $category->delete();
    }
}
