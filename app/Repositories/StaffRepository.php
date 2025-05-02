<?php

namespace App\Repositories;

use App\Exceptions\StaffNotFoundException;
use App\Models\User;

class StaffRepository implements \App\Contracts\Staff
{
    public function paginate($each = 10): \Illuminate\Pagination\LengthAwarePaginator
    {
        return User::whereHas(
            'role',
            function ($query) {
                $query->where('name', 'staff');
            }
        )
        ->paginate($each);
    }

    public function find(int $id): User
    {
        $staff = User::whereHas(
            'role',
            function ($query) {
                $query->where('name', 'staff');
            }
        )
        ->where('id', $id)
        ->firstOr(function () {
            throw new StaffNotFoundException();
        });

        return $staff;
    }

    public function create(\App\DTOs\CreateUserDTO $dto): User
    {
        $staff = User::create($dto->toArray());

        return $staff;
    }

    public function update(int $id, \App\DTOs\UpdateUserDTO $dto): User
    {
        $staff = User::whereHas(
            'role',
            function ($query) {
                $query->where('name', 'staff');
            }
        )
        ->where('id', $id)
        ->firstOr(function () {
            throw new StaffNotFoundException();
        });

        $staff->update($dto->toArray());

        return $staff;
    }

    public function delete(int $id): bool
    {
        $staff = User::whereHas(
            'role',
            function ($query) {
                $query->where('name', 'staff');
            }
        )
        ->where('id', $id)
        ->firstOr(function () {
            throw new StaffNotFoundException();
        });

        return $staff->delete();
    }
}
