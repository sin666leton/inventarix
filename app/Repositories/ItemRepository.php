<?php
namespace App\Repositories;

use App\Exceptions\ItemNotFoundException;
use App\Models\Item;

class ItemRepository implements \App\Contracts\Item
{
    public function paginate(int $category_id, int $perpage = 10): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Item::where('category_id', $category_id)
            ->paginate($perpage);
    }

    public function find(int $id): Item
    {
        $item = Item::where('id', $id)
            ->firstOr(function () {
                throw new ItemNotFoundException();
            });

        return $item;
    }

    public function getStock(int $id): int
    {
        $item = Item::select(['id', 'stock'])
            ->where('id', $id)
            ->firstOr(function () {
                throw new ItemNotFoundException();
            });

        return $item->value('stock');
    }

    public function updateStock(int $id, int $amount): bool
    {
        $item = Item::select(['id', 'stock'])
            ->where('id', $id)
            ->firstOr(function () {
                throw new ItemNotFoundException();
            });

        return $item->update([
            'stock' => $amount
        ]);
    }

    public function create(int $category_id, \App\DTOs\CreateItemDTO $dto): Item
    {
        $item = Item::create(['category_id' => $category_id, ...$dto->toArray()]);

        return $item;
    }

    public function update(int $id, \App\DTOs\UpdateItemDTO $dto): Item
    {
        $item = Item::where('id', $id)
            ->firstOr(function () {
                throw new ItemNotFoundException();
            });

        $item->update($dto->toArray());

        return $item;
    }

    public function delete(int $id): bool
    {
        $item = Item::where('id', $id)
            ->firstOr(function () {
                throw new ItemNotFoundException();
            });

        return $item->delete();
    }
}
