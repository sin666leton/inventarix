<?php
namespace App\Contracts;

use App\DTOs\CreateItemDTO;
use App\DTOs\UpdateItemDTO;
use Illuminate\Pagination\LengthAwarePaginator;

interface Item
{
    /**
     * Paginasi item berdasarkan ID kategori
     * 
     * @param int $category_id
     * @param int $perpage
     * @return LengthAwarePaginator
     */
    public function paginate(int $category_id, int $perpage = 10): LengthAwarePaginator;

    /**
     * Cari item berdasarkan ID
     * @param int $id
     * 
     * @return \App\Models\Item
     */
    public function find(int $id): \App\Models\Item;

    /**
     * Buat item
     * 
     * @param int $category_id
     * @param \App\DTOs\CreateItemDTO $dto
     * @return \App\Models\Item
     */
    public function create(int $category_id, CreateItemDTO $dto): \App\Models\Item;

    /**
     * Perbarui item
     * 
     * @param int $id
     * @param \App\DTOs\UpdateItemDTO $dto
     * @return \App\Models\Item
     */
    public function update(int $id, UpdateItemDTO $dto): \App\Models\Item;

    /**
     * Hapus item
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Ambil stock pada item
     * 
     * @param int $id
     * @return int
     */
    public function getStock(int $id): int;

    /**
     * Perbarui stock
     * 
     * @param int $id
     * @param int $amount
     * @return bool
     */
    public function updateStock(int $id, int $amount): bool;
}