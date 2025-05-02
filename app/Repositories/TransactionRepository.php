<?php
namespace App\Repositories;

use App\Exceptions\TransactionNotFound;
use App\Models\Transaction;

class TransactionRepository implements \App\Contracts\Transaction
{
    public function find(int $id): Transaction
    {
        $transaction = Transaction::where('id', $id)
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

    public function findWithUserAndItem(int $id): Transaction
    {
        $transaction = Transaction::with([
                'user' => function ($query) {
                    $query->select(['id', 'name']);
                },
                'item' => function ($query) {
                    $query->select(['id', 'name']);
                }
            ])
            ->where('id', $id)
            ->firstOr(function () {
                throw new TransactionNotFound();
            });

        return $transaction;
    }

    public function findStaffTransaction(int $user_id, int $id): Transaction
    {
        $transaction = Transaction::where('id', $id)
            ->where('user_id', $user_id)
            ->firstOr(function () {
                throw new TransactionNotFound();
            });

        return $transaction;
    }
}
