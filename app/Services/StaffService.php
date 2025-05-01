<?php
namespace App\Services;

use App\Contracts\Staff;
use App\DTOs\CreateUserDTO;
use App\DTOs\UpdateUserDTO;
use Illuminate\Support\Facades\Cache;

class StaffService
{
    public function __construct(
        protected Staff $userRepository
    ) {}

    public function paginateStaff(int $each = 10)
    {
        return $this->userRepository->paginate($each);
    }

    public function findStaff(int $id)
    {
        $user = Cache::remember("staff_$id", 3600, fn() => $this->userRepository->find($id));
    
        return $user;
    }

    public function createStaff(CreateUserDTO $dto)
    {
        $user = $this->userRepository->create($dto);

        return $user;
    }

    public function updateStaff(int $id, UpdateUserDTO $dto)
    {
        $user = $this->userRepository->update($id, $dto);

        Cache::forget("staff_$id");

        return $user;
    }

    public function deleteStaff(int $id)
    {
        $result = $this->userRepository->delete($id);

        if ($result) Cache::forget("staff_$id");

        return $result;
    }
}