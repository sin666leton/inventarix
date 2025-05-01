<?php

namespace Tests\Feature\Services;

use App\Exceptions\InvalidCredentialException;
use App\Models\Role;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    protected $authService;

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
            'password' => Hash::make('123')
        ]);

        $this->staff = User::create([
            'role_id' => $staff->id,
            'name' => 'staff',
            'email' => 'staff@example.com',
            'password' => Hash::make('123')
        ]);

        $this->authService = $this->app->make(AuthService::class);

        Cache::flush();
    }

    public function test_loginAsAdmin_throws_InvalidCredentialException()
    {
        $this->expectException(InvalidCredentialException::class);
        $this->expectExceptionMessage('Wrong email or password.');
        $this->expectExceptionCode(401);

        $email = 'cihuy@example.com';
        $password = 'abc';

        $this->authService->loginAsAdmin($email, $password);
    }

    public function test_loginAsAdmin_with_valid_credential_and_return_token()
    {
        $email = 'admin@example.com';
        $password = '123';

        $token = $this->authService->loginAsAdmin($email, $password);

        $this->assertIsString($token);
        $this->assertEquals($email, Auth::user()->email);
    }

    public function test_loginAsStaff_throws_InvalidCredentialException()
    {
        $this->expectException(InvalidCredentialException::class);
        $this->expectExceptionMessage('Wrong email or password.');
        $this->expectExceptionCode(401);

        $email = 'cihuy@example.com';
        $password = 'abc';

        $this->authService->loginAsStaff($email, $password);
    }

    public function test_loginAsStaff_with_valid_credential_and_return_token()
    {
        $email = 'staff@example.com';
        $password = '123';

        $token = $this->authService->loginAsStaff($email, $password);

        $this->assertIsString($token);
        $this->assertEquals($email, Auth::user()->email);
    }

    public function test_logoutAnyRole_as_admin()
    {
        Sanctum::actingAs($this->admin)->createToken('testToken', config('ability.admin'));

        $result = $this->authService->logoutAnyRole();
        
        $this->assertTrue($result);
    }

    public function test_logoutAnyRole_as_staff()
    {
        Sanctum::actingAs($this->staff)->createToken('testToken', config('ability.admin'));

        $result = $this->authService->logoutAnyRole();
        
        $this->assertTrue($result);
    }
}
