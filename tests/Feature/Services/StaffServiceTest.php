<?php

namespace Tests\Feature\Services;

use App\DTOs\CreateUserDTO;
use App\DTOs\UpdateUserDTO;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class StaffServiceTest extends TestCase
{
    protected $staffService;

    protected $staffRole;

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->staffService = $this->app->make(\App\Services\StaffService::class);
        $this->staffRole = Role::create(['name' => 'staff']);
    }

    public function test_find_staff_throws_ModelNotFoundException_with_invalid_id()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage("User Not Found.");
        $this->expectExceptionCode(404);

        $this->staffService->findStaff(9999);
    }

    public function test_find_staff_should_forget_cache_and_return_User_role_staff_with_valid_id()
    {
        $staff = User::create([
            'role_id' => $this->staffRole->id,
            'name' => 'test',
            'email' => 'testemail@example.com',
            'password' => '123'
        ]);

        $result = $this->staffService->findStaff($staff->id);
        
        $this->assertInstanceOf(User::class, $result);
    }

    public function test_paginate_staff_return_LengthAwarePaginator()
    {
        User::create([
            'role_id' => $this->staffRole->id,
            'name' => 'test',
            'email' => 'testemail@example.com',
            'password' => '123'
        ]);

        $result = $this->staffService->paginateStaff();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    public function test_create_staff_should_return_User()
    {
        $dto = new CreateUserDTO('test', 'test@example.id', 'abcde233', $this->staffRole->id);
        $result = $this->staffService->createStaff($dto);

        $this->assertInstanceOf(User::class, $result);
    }

    public function test_update_staff_throws_ModelNotFoundException()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage("User Not Found.");
        $this->expectExceptionCode(404);

        $this->staffService->updateStaff(999, new UpdateUserDTO('testing', 'cihuyy@example.cp,'));
    }

    public function test_update_staff_should_forget_cache_and_return_User()
    {
        $staff = User::create([
            'role_id' => $this->staffRole->id,
            'name' => 'test',
            'email' => 'testemail@example.com',
            'password' => '123'
        ]);

        $result = $this->staffService->updateStaff($staff->id, new UpdateUserDTO('testing', 'cihuyy@example.com'));
        
        $this->assertEquals(null, Cache::get("staff_".$staff->id));
        $this->assertInstanceOf(User::class, $result);
        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'name' => 'testing',
            'email' => 'cihuyy@example.com'
        ]);
    }

    public function test_delete_staff_throws_ModelNotFoundException()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage("User Not Found.");
        $this->expectExceptionCode(404);

        $this->staffService->deleteStaff(999);
    }

    public function test_delete_staff_should_forget_cache_and_return_bool()
    {
        $staff = User::create([
            'role_id' => $this->staffRole->id,
            'name' => 'test',
            'email' => 'testemail@example.com',
            'password' => '123'
        ]);

        $result = $this->staffService->deleteStaff($staff->id);
        
        $this->assertEquals(true, $result);
        $this->assertEquals(null, Cache::get("staff_".$staff->id));
        $this->assertDatabaseMissing('users', [
            'role_id' => $this->staffRole->id,
            'name' => 'test',
            'email' => 'testemail@example.com',
        ]);
    }
}
