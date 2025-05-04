<?php

namespace Tests\Unit\Service;

use App\Exceptions\InvalidCredentialException;
use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Cache;
use Mockery;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    protected $userRepository;

    protected $userService;

    protected $logManagerMock;

    protected $hashMock;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var \App\Contracts\User&\Mockery\MockInterface */
        $this->userRepository = Mockery::mock(\App\Contracts\User::class);

        /** @var Hasher&Mockery\MockInterface */
        $this->hashMock = Mockery::mock(Hasher::class);

        /** @var LogManager&Mockery\MockInterface */
        $this->logManagerMock = Mockery::mock(LogManager::class);

        $this->userService = new UserService($this->userRepository, $this->hashMock, $this->logManagerMock);

    }

    protected function tearDown(): void
    {
        Cache::swap(new \Illuminate\Cache\Repository(new \Illuminate\Cache\ArrayStore()));
        Mockery::close();

        parent::tearDown();
        
    }


    public function test_update_name_as_staff_should_forget_cache_and_return_bool()
    {
        Cache::spy();

        $newName = 'Ziddd';
        $userID = 1;

        $role = new Role([
            'id' => 2,
            'name' => 'staff'
        ]);

        $user = new User([
            'id' => $userID,
            'name' => 'Zidan',
            'email' => 'zidan@example.com',
            'role_id' => $role->id
        ]);

        $user->setRelation('role', $role);

        $this->userRepository
            ->shouldReceive('updateName')
            ->once()
            ->with($user, $newName)
            ->andReturn(true);

        $this->logManagerMock
            ->shouldReceive('channel')
            ->once()
            ->with('model')
            ->andReturnSelf();

        $this->logManagerMock
            ->shouldReceive('info')
            ->once();

        $result = $this->userService->updateName($user, $newName);
    
        Cache::shouldHaveReceived("forget")
            ->once()
            ->with("staff_".$user->id);

        $this->assertTrue($result);
    }

    public function test_update_name_as_admin_should_forget_cache_and_return_bool()
    {
        Cache::spy();

        $newName = 'Ziddd';
        $userID = 1;

        $role = new Role([
            'id' => 1,
            'name' => 'admin'
        ]);

        $user = new User([
            'id' => $userID,
            'name' => 'Zidan',
            'email' => 'zidan@example.com',
            'role_id' => $role->id
        ]);

        $user->setRelation('role', $role);

        $this->userRepository
            ->shouldReceive('updateName')
            ->once()
            ->with($user, $newName)
            ->andReturn(true);

        $this->logManagerMock
            ->shouldReceive('channel')
            ->once()
            ->with('model')
            ->andReturnSelf();

        $this->logManagerMock
            ->shouldReceive('info')
            ->once();

        $result = $this->userService->updateName($user, $newName);
    
        Cache::shouldHaveReceived("forget")
            ->once()
            ->with("admin_".$user->id);

        $this->assertTrue($result);
    }

    public function test_update_email_throws_InvalidCredentialException_with_invalid_password()
    {
        $this->expectException(InvalidCredentialException::class);
        $this->expectExceptionMessage('Wrong password.');
        $this->expectExceptionCode(401);

        $password = 'abcde2139';

        $user = new User([
            'name' => 'Zidan',
            'email' => 'zidan@example.com',
        ]);

        $this->hashMock
            ->shouldReceive('check')
            ->once()
            ->with($password, $user->password)
            ->andReturn(false);

        $this->userService->updateEmail($user, 'newEmail', $password);
    }

    public function test_update_email_as_staff_should_forget_cache_and_return_true()
    {
        Cache::spy();

        $password = 'abcde2139';

        $role = new Role([
            'id' => 2,
            'name' => 'staff'
        ]);

        $user = new User([
            'role_id' => $role->id,
            'name' => 'Zidan',
            'email' => 'zidan@example.com',
        ]);

        $user->setRelation('role', $role);

        $this->hashMock
            ->shouldReceive('check')
            ->once()
            ->with($password, $user->password)
            ->andReturn(true);

        $this->userRepository
            ->shouldReceive('updateEmail')
            ->once()
            ->with($user, 'newEmail')
            ->andReturn(true);
            
        $this->logManagerMock
            ->shouldReceive('channel')
            ->once()
            ->with('model')
            ->andReturnSelf();

        $this->logManagerMock
            ->shouldReceive('info')
            ->once();

        $result = $this->userService->updateEmail($user, 'newEmail', $password);
    
        Cache::shouldHaveReceived('forget')
            ->once()
            ->with("staff_".$user->id);

        $this->assertTrue($result);

    }

    public function test_update_email_as_admin_should_forget_cache_and_return_true()
    {
        Cache::spy();

        $password = 'abcde2139';

        $role = new Role([
            'id' => 1,
            'name' => 'admin'
        ]);

        $user = new User([
            'role_id' => $role->id,
            'name' => 'Zidan',
            'email' => 'zidan@example.com',
        ]);

        $user->setRelation('role', $role);

        $this->hashMock
            ->shouldReceive('check')
            ->once()
            ->with($password, $user->password)
            ->andReturn(true);

        $this->userRepository
            ->shouldReceive('updateEmail')
            ->once()
            ->with($user, 'newEmail')
            ->andReturn(true);

        $this->logManagerMock
            ->shouldReceive('channel')
            ->once()
            ->with('model')
            ->andReturnSelf();

        $this->logManagerMock
            ->shouldReceive('info')
            ->once();

        $result = $this->userService->updateEmail($user, 'newEmail', $password);
    
        $this->assertTrue($result);

        Cache::shouldHaveReceived('forget')
            ->once()
            ->with("admin_".$user->id);
    }

    public function test_update_password_throws_InvalidCredentialException_with_invalid_password()
    {
        $this->expectException(InvalidCredentialException::class);
        $this->expectExceptionMessage('Wrong password.');
        $this->expectExceptionCode(401);

        $oldInputPassword = 'oldPassword';

        $user = new User([
            'name' => 'Zidan',
            'email' => 'zidan@example.com',
        ]);

        $this->hashMock
            ->shouldReceive('check')
            ->once()
            ->with($oldInputPassword, $user->password)
            ->andReturn(false);

        $this->userService->updatePassword($user, $oldInputPassword, 'newPassword');
    }

    public function test_update_password_as_staff_should_forget_cache_and_return_true()
    {
        Cache::spy();

        $oldInputPassword = '1232451251';
        $newPassword = 'test2135';

        $role = new Role([
            'id' => 2,
            'name' => 'staff'
        ]);

        $user = new User([
            'role_id' => $role->id,
            'name' => 'Zidan',
            'email' => 'zidan@example.com',
        ]);

        $user->setRelation('role', $role);

        $this->hashMock
            ->shouldReceive('check')
            ->once()
            ->with($oldInputPassword, $user->password)
            ->andReturn(true);
        
        $this->hashMock
            ->shouldReceive('make')
            ->once()
            ->with($newPassword)
            ->andReturn('Hash-password');

        $this->userRepository
            ->shouldReceive('updatePassword')
            ->once()
            ->with($user, 'Hash-password')
            ->andReturn(true);

        $this->logManagerMock
            ->shouldReceive('channel')
            ->once()
            ->with('model')
            ->andReturnSelf();

        $this->logManagerMock
            ->shouldReceive('info')
            ->once();

        $result = $this->userService->updatePassword($user, $oldInputPassword, $newPassword);
        
        Cache::shouldHaveReceived('forget')
            ->once()
            ->with("staff_".$user->id);

        $this->assertTrue($result);
    }

    public function test_update_password_as_admin_should_forget_cache_and_return_true()
    {
        Cache::spy();

        $password = 'abcde2139';
        $oldInputPassword = $password;
        $newPassword = 'testing21312';

        $role = new Role([
            'id' => 1,
            'name' => 'admin'
        ]);

        $user = new User([
            'role_id' => $role->id,
            'name' => 'Zidan',
            'email' => 'zidan@example.com',
        ]);

        $user->setRelation('role', $role);

        $this->hashMock
            ->shouldReceive('check')
            ->once()
            ->with($oldInputPassword, $user->password)
            ->andReturn(true);

        $this->hashMock
            ->shouldReceive('make')
            ->once()
            ->with($newPassword)
            ->andReturn('Hash-password');

        $this->userRepository
            ->shouldReceive('updatePassword')
            ->once()
            ->with($user, 'Hash-password')
            ->andReturn(true);

        $this->logManagerMock
            ->shouldReceive('channel')
            ->once()
            ->with('model')
            ->andReturnSelf();

        $this->logManagerMock
            ->shouldReceive('info')
            ->once();

        $result = $this->userService->updatePassword($user, $oldInputPassword, $newPassword);
        
        Cache::shouldHaveReceived('forget')
            ->once()
            ->with("admin_".$user->id);

        $this->assertTrue($result);
    }
}
