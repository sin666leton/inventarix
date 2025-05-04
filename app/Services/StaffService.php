<?php
namespace App\Services;

use App\Contracts\Staff;
use App\DTOs\CreateUserDTO;
use App\DTOs\UpdateUserDTO;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Cache;

class StaffService
{
    public function __construct(
        protected Staff $userRepository,
        protected LogManager $logger
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

        $this->logger->channel('model')->info('Create staff.', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email
        ]);

        return $user;
    }

    public function updateStaff(int $id, UpdateUserDTO $dto)
    {
        $user = $this->userRepository->update($id, $dto);

        Cache::forget("staff_$id");
        $this->logger->channel('model')->info('Update staff.', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email
        ]);

        return $user;
    }

    public function deleteStaff(int $id)
    {
        $result = $this->userRepository->delete($id);

        if ($result) {
            Cache::forget("staff_$id");
            $this->logger->channel('model')->info('Delete staff.', [
                'id' => $id,
            ]);
        }

        return $result;
    }
}