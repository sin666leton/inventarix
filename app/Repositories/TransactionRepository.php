<?php
namespace App\Repositories;

use App\Exceptions\TransactionNotFound;
use App\Models\Transaction;

class TransactionRepository implements \App\Contracts\Transaction
{
    public function find(int $id): Transaction
    {
        $transaction = Transaction::select(['id', 'user_id', 'item_id', 'type', 'quantity', 'description', 'created_at'])
            ->where('id', $id)
            ->firstOr(function () {
                throw new TransactionNotFound();
            });

        return $transaction;
    }

    public function paginate($item = 10): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Transaction::paginate($item);
    }

    public function create(\App\DTOs\TransactionDTO $dto): Transaction
    {
        $transaction = Transaction::create($dto->toArray());

        return $transaction;
    }

    public function delete(int $id): bool
    {
        $transaction = Transaction::where('id', $id)
            ->firstOr(function () {
                throw new TransactionNotFound();
            });

        return $transaction->delete();
    }
}
