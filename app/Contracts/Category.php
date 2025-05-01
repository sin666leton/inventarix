<?php

namespace App\Contracts;

use App\DTOs\CategoryDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface Category
{
    public function paginate(int $item = 10): LengthAwarePaginator;

    /**
     * Cari kategori berdasarkan ID
     * @param int $id
     * @return \App\Models\Category
     */
    public function find(int $id): \App\Models\Category;

    /**
     * Ambil semua kategori
     * 
     * @return Collection<int, \App\Models\Category>
     */
    public function all(): Collection;

    /**
     * Buat kategori
     * 
     * @param \App\DTOs\CategoryDTO $dto
     * @return \App\Models\Category
     */
    public function create(CategoryDTO $dto): \App\Models\Category;

    /**
     * Perbarui kategori
     * 
     * @param int $id
     * @param \App\DTOs\CategoryDTO $dto
     * @return \App\Models\Category
     */
    public function update(int $id, CategoryDTO $dto): \App\Models\Category;

    /**
     * Hapus kategori
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Apakah kategori tersedia?
     * 
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool;
}
