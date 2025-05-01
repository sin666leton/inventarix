<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Exceptions\InvalidCredentialException;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
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

        Cache::flush();
    }

    public function test_login_as_admin_throws_InvalidCredentialException_with_invalid_credentials()
    {
        $this->postJson(route('v1.auth.admin.login'), [
            'email' => 'testing@example.com',
            'password' => 'abcd'
        ])
        ->assertStatus(401)
        ->assertJsonStructure([
            'message'
        ], [
            'message' => 'Wrong email or password.'
        ]);
    }

    public function test_login_as_staff_throws_InvalidCredentialException_with_invalid_credentials()
    {
        $this->postJson(route('v1.auth.staff.login'), [
            'email' => 'testing@example.com',
            'password' => 'abcd'
        ])
        ->assertStatus(401)
        ->assertJsonStructure([
            'message'
        ], [
            'message' => 'Wrong email or password.'
        ]);
    }

    public function test_login_as_admin_with_valid_credentials_and_return_auth_token()
    {
        $this->postJson(route('v1.auth.admin.login'), [
            'email' => 'admin@example.com',
            'password' => '123'
        ])
        ->assertStatus(200)
        ->assertJsonStructure([
            'token'
        ]);
    }
    
    public function test_login_as_staff_with_valid_credentials_and_return_auth_token()
    {
        $this->postJson(route('v1.auth.admin.login'), [
            'email' => 'admin@example.com',
            'password' => '123'
        ])
        ->assertStatus(200)
        ->assertJsonStructure([
            'token'
        ]);
    }

    public function test_logout_without_auth_account_throws_InvalidCredentialException()
    {
        $this->deleteJson(route('v1.auth.admin.logout'))
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ], [
                'message' => 'Wrong email or password.'
            ]);

        $this->deleteJson(route('v1.auth.staff.logout'))
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ], [
                'message' => 'Wrong email or password.'
            ]);
    }

    public function test_logout_with_admin_account_and_return_message()
    {
        Sanctum::actingAs($this->admin, config('ability.admin'));

        $this->deleteJson(route('v1.auth.admin.logout'))
            ->assertStatus(200)
            ->assertJsonStructure([
                'message'
            ], [
                'message' => 'Logged out successfully.'
            ]);
    }
    
    public function test_logout_with_staff_account_and_return_message()
    {
        Sanctum::actingAs($this->staff, config('ability.staff'));

        $this->deleteJson(route('v1.auth.staff.logout'))
            ->assertStatus(200)
            ->assertJsonStructure([
                'message'
            ], [
                'message' => 'Logged out successfully.'
            ]);
    }
}
