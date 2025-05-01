<?php

namespace Tests\Unit\Service;

use App\DTOs\CreateUserDTO;
use App\DTOs\UpdateUserDTO;
use App\Models\User;
use App\Services\StaffService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Mockery;
use PHPUnit\Framework\TestCase;

class StaffServiceTest extends TestCase
{
    protected $staffRepository;

    protected $staffService;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var \App\Contracts\Staff&Mockery\MockInterface */
        $this->staffRepository = Mockery::mock(\App\Contracts\Staff::class);

        $this->staffService = new StaffService($this->staffRepository);
    }

    protected function tearDown(): void
    {
        Cache::swap(new \Illuminate\Cache\Repository(new \Illuminate\Cache\ArrayStore()));
        Mockery::close();

        parent::tearDown();
    }

    public function test_find_staff_throws_ModelNotFoundException_with_invalid_id()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('User Not Found.');
        $this->expectExceptionCode(404);

        $id = 999;

        $this->staffRepository
            ->shouldReceive('find')
            ->once()
            ->with($id)
            ->andThrowExceptions([
                new ModelNotFoundException("User Not Found.", 404)
            ]);

        Cache::shouldReceive('remember')
            ->once()
            ->with("staff_$id", 3600, \Closure::class)
            ->andReturnUsing(function ($key, $ttl, $closure) {
                return $closure();
            });

        $this->staffService->findStaff($id);

    }

    public function test_find_staff_return_User_role_staff_with_valid_id()
    {
        $id = 1;

        $this->staffRepository
            ->shouldReceive('find')
            ->once()
            ->with($id)
            ->andReturn(new User());

        Cache::shouldReceive('remember')
            ->once()
            ->with("staff_$id", 3600, \Closure::class)
            ->andReturnUsing(function ($key, $ttl, $closure) {
                return $closure();
            });

        $result = $this->staffService->findStaff($id);

        $this->assertInstanceOf(User::class, $result);
    }

    public function test_paginate_staff_return_LengthAwarePaginator()
    {
        $paginator = new LengthAwarePaginator(
            collect([
                new User(),
                new User(),
                new User(),
            ]),
            3,
            10,
            1
        );

        $this->staffRepository
            ->shouldReceive('paginate')
            ->once()
            ->with(10)
            ->andReturn($paginator);

        $result = $this->staffService->paginateStaff(10);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    public function test_create_staff_should_return_User()
    {
        Hash::spy();
        $dto = new CreateUserDTO('Lorem', 'testlorem@example.com', '123', 2);

        $this->staffRepository
            ->shouldReceive('create')
            ->once()
            ->with($dto)
            ->andReturn(new User($dto->toArray()));

        $result = $this->staffService->createStaff($dto);

        Hash::shouldHaveReceived('make')
            ->once()
            ->with($dto->password);

        $this->assertInstanceOf(User::class, $result);
    }

    public function test_update_staff_throws_ModelNotFoundException()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('User Not Found.');
        $this->expectExceptionCode(404);

        $id = 999;
        $dto = new UpdateUserDTO('test', 'test@example.com');

        $this->staffRepository
            ->shouldReceive('update')
            ->once()
            ->with($id, $dto)
            ->andThrowExceptions([
                new ModelNotFoundException("User Not Found.", 404)
            ]);

        $this->staffService->updateStaff($id, $dto);
    }

    public function test_update_staff_should_forget_cache_and_return_User()
    {
        Cache::spy();

        $id = 1;
        $dto = new UpdateUserDTO('test', 'test@example.com');

        $this->staffRepository
            ->shouldReceive('update')
            ->once()
            ->with($id, $dto)
            ->andReturn(new User());

        $result = $this->staffService->updateStaff($id, $dto);

        Cache::shouldHaveReceived('forget')
            ->once()
            ->with("staff_$id");

        $this->assertInstanceOf(User::class, $result);
    }

    public function test_delete_staff_throws_ModelNotFoundException()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('User Not Found.');
        $this->expectExceptionCode(404);

        $id = 999;

        $this->staffRepository
            ->shouldReceive('delete')
            ->once()
            ->with($id)
            ->andThrowExceptions([
                new ModelNotFoundException('User Not Found.', 404)
            ]);

        $this->staffService->deleteStaff($id);
    }

    public function test_delete_staff_should_forget_cache_and_return_bool()
    {
        Cache::spy();
        $id = 1;

        $this->staffRepository
            ->shouldReceive('delete')
            ->once()
            ->with($id)
            ->andReturn(true);

        $result = $this->staffService->deleteStaff($id);
    
        Cache::shouldHaveReceived('forget')
            ->once()
            ->with("staff_$id");

        $this->assertTrue($result);
    }
}
