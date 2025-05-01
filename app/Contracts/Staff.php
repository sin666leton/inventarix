<?php

namespace App\Contracts;

use App\DTOs\CreateUserDTO;
use App\DTOs\UpdateUserDTO;
use Illuminate\Pagination\LengthAwarePaginator;

interface Staff
{
    /**
     * Paginasi staff
     * 
     * @param mixed $each
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate($each = 10): LengthAwarePaginator;

    /**
     * Cari staff berdasarkan ID
     * 
     * @param int $id
     * @return \App\Models\User
     */
    public function find(int $id): \App\Models\User;

    /**
     * Buat user staff
     * 
     * @param \App\DTOs\CreateUserDTO $dto
     * @return \App\Models\User
     */
    public function create(CreateUserDTO $dto): \App\Models\User;

    /**
     * Perbarui staff
     * 
     * @param int $id
     * @param \App\DTOs\UpdateUserDTO $dto
     * @return\App\Models\User
     */
    public function update(int $id, UpdateUserDTO $dto): \App\Models\User;

    /**
     * Hapus staff
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
