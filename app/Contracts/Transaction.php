<?php

namespace App\Contracts;

use App\DTOs\TransactionDTO;
use Illuminate\Pagination\LengthAwarePaginator;

interface Transaction
{
    /**
     * Cari transaksi berdasarkan id
     * 
     * @param int $id
     * @return \App\Models\Transaction
     */
    public function find(int $id): \App\Models\Transaction;

    /**
     * Paginasi transaksi
     * 
     * @param mixed $item
     * @return LengthAwarePaginator
     */
    public function paginate($item = 10): LengthAwarePaginator;

    /**
     * Buat transaksi baru
     * 
     * @param \App\DTOs\TransactionDTO $dto
     * @return \App\Models\Transaction
     */
    public function create(TransactionDTO $dto): \App\Models\Transaction;

    /**
     * Hapus transaksi
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Cari transaksi beserta pengguna dan itemnya
     * 
     * @param int $id
     * @return \App\Models\Transaction
     */
    public function findWithUserAndItem(int $id): \App\Models\Transaction;

    /**
     * Cari transaksi staff
     * 
     * @param int $user_id
     * @param int $id
     * @return \App\Models\Transaction
     */
    public function findStaffTransaction(int $user_id, int $id): \App\Models\Transaction;
}
