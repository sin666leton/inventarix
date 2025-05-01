<?php

namespace Tests\Feature\Services;

use App\Exceptions\InvalidCredentialException;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    protected $userService;

    protected $admin;

    protected $staff;

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = Role::create([
            'name' => 'admin'
        ]);

        $staff = Role::create([
            'name' => 'staff'
        ]);

        $this->admin = User::create([
            'role_id' => $admin->id,
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => 'admin12345'
        ]);

        $this->staff = User::create([
            'role_id' => $staff->id,
            'name' => 'staff',
            'email' => 'staff@example.com',
            'password' => 'staff12345'
        ]);
        
        $this->userService = $this->app->make(\App\Services\UserService::class);

        Cache::flush();

        Cache::put("admin_".$this->admin->id, $this->admin, 3600);
        Cache::put("staff_".$this->staff->id, $this->staff, 3600);
    }

    public function test_update_name_as_staff_should_forget_cache_and_return_bool()
    {
        $this->assertEquals($this->staff, Cache::get("staff_".$this->staff->id));

        $result = $this->userService->updateName($this->staff, 'Zidan');

        $this->assertTrue($result);
        $this->assertEquals(null, Cache::get("staff_".$this->staff->id));
        $this->assertDatabaseHas('users', [
            'id' => $this->staff->id,
            'name' => 'Zidan'
        ]);
    }

    public function test_update_name_as_admin_should_forget_cache_and_return_bool()
    {
        $this->assertEquals($this->admin, Cache::get("admin_".$this->admin->id));

        $result = $this->userService->updateName($this->admin, 'Zidan');

        $this->assertTrue($result);
        $this->assertEquals(null, Cache::get("admin_".$this->admin->id));
        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'name' => 'Zidan'
        ]);
    }

    public function test_update_email_throws_InvalidCredentialException_with_invalid_password()
    {
        $this->expectException(InvalidCredentialException::class);
        $this->expectExceptionMessage('Wrong password.');
        $this->expectExceptionCode(401);

        $this->userService->updateEmail($this->admin, 'new.email@example.com', 'abdkajwbdkajwbd');
    
        $this->assertEquals($this->admin, Cache::get("admin_".$this->admin->id));
        $this->assertDatabaseMissing('users', [
            'id' => $this->admin->id,
            'email' => 'new.email@example.com'
        ]);
    }

    public function test_update_email_as_staff_should_forget_cache_and_return_true()
    {
        $newEmail = 'mynewawesomeemail@gmail.com';

        $this->assertEquals($this->staff, Cache::get("staff_".$this->staff->id));

        $result = $this->userService->updateEmail($this->staff, $newEmail, 'staff12345');
    
        $this->assertTrue($result);
        $this->assertEquals(null, Cache::get('staff_'.$this->staff->id));
        $this->assertDatabaseHas('users', [
            'id' => $this->staff->id,
            'email' => $newEmail
        ]);
    }

    public function test_update_email_as_admin_should_forget_cache_and_return_true()
    {
        $newEmail = 'mynewawesomeemail@gmail.com';

        $this->assertEquals($this->admin, Cache::get("admin_".$this->admin->id));

        $result = $this->userService->updateEmail($this->admin, $newEmail, 'admin12345');
    
        $this->assertTrue($result);
        $this->assertEquals(null, Cache::get('admin_'.$this->admin->id));
        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'email' => $newEmail
        ]);
    }

    public function test_update_password_throws_InvalidCredentialException_with_invalid_password()
    {
        $this->expectException(InvalidCredentialException::class);
        $this->expectExceptionMessage('Wrong password.');
        $this->expectExceptionCode(401);

        $this->userService->updatePassword($this->admin, 'wndkajwndkaw', 'newPassword');
        $this->assertEquals($this->admin, Cache::get('admin_'.$this->admin->id));
    }

    public function test_update_password_as_staff_should_forget_cache_and_return_true()
    {
        $this->assertEquals($this->staff, Cache::get("staff_".$this->staff->id));

        $result = $this->userService->updatePassword($this->staff, 'staff12345', 'staff888');
    
        $this->assertTrue($result);
        $this->assertEquals(null, Cache::get("staff_".$this->staff->id));
    }

    public function test_update_password_as_admin_should_forget_cache_and_return_true()
    {
        $this->assertEquals($this->admin, Cache::get("admin_".$this->admin->id));

        $result = $this->userService->updatePassword($this->admin, 'admin12345', 'admin888');
    
        $this->assertTrue($result);
        $this->assertEquals(null, Cache::get("admin_".$this->admin->id));
    }
}
