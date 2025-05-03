<?php
namespace App\Services;

use App\Contracts\Category;
use App\Contracts\Item;
use App\DTOs\CreateItemDTO;
use App\DTOs\ItemDTO;
use App\DTOs\UpdateItemDTO;
use App\Exceptions\InsufficientStockException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Cache;

class ItemService
{
    public function __construct(
        protected Item $itemRepository,
        protected Category $categoryRepository,
        protected LogManager $logger
    ) {}

    public function paginateItems(int $category_id, int $perPage = 10)
    {
        if (!$this->categoryRepository->exists($category_id)) throw new ModelNotFoundException("Category Not Found.", 404);

        $itemPaginator = $this->itemRepository->paginate($category_id, $perPage);

        return $itemPaginator;
    }

    public function findItem(int $id)
    {
        $item = Cache::remember("item_$id", 3600, fn() => $this->itemRepository->find($id));
    
        return $item;
    }

    public function createItem(int $category_id, CreateItemDTO $dto)
    {
        if (!$this->categoryRepository->exists($category_id)) throw new ModelNotFoundException("Category Not Found.", 404);
        
        $item = $this->itemRepository->create($category_id, $dto);

        $this->logger->channel('model')->info('Create item.', [
            'id' => $item->id,
            'name' => $item->name
        ]);

        return $item;
    }

    public function updateItem(int $id, UpdateItemDTO $dto)
    {
        $item = $this->itemRepository->update($id, $dto);

        Cache::forget("item_$id");
        $this->logger->channel('model')->info('Update item.', [
            'id' => $item->id,
            'name' => $item->name
        ]);

        return $item;
    }

    public function deleteItem(int $id)
    {
        $result = $this->itemRepository->delete($id);

        if ($result) {
            Cache::forget("item_$id");
            $this->logger->channel('model')->info('Delete item.', [
                'id' => $id,
            ]);
        }

        return $result;
    }

    public function decrementStockItem(int $id, int $amount)
    {
        $currentStock = $this->itemRepository->getStock($id);
        if ($currentStock < $amount) throw new InsufficientStockException();

        $result = $this->itemRepository->updateStock($id, $currentStock - $amount);

        if ($result) {
            Cache::forget("item_$id");
            $this->logger->channel('stocks')->info('Decrement stock item.', [
                'id' => $id,
                'amount' => $amount
            ]);
        }

        return $result;
    }

    public function incrementStockItem(int $id, int $amount)
    {
        $currentStock = $this->itemRepository->getStock($id);

        $newStock = $currentStock + $amount;

        $result = $this->itemRepository->updateStock($id, $newStock);

        if ($result) {
            Cache::forget("item_$id");
            $this->logger->channel('stocks')->info('Increment stock item.', [
                'id' => $id,
                'amount' => $amount
            ]);
        }
        
        return $result;
    }

    public function getStockItem(int $id)
    {
        $stock = $this->itemRepository->getStock($id);
        
        return $stock;
    }
}