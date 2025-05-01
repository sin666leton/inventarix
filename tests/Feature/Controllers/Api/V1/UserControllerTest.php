<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserControllerTest extends TestCase
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
        Sanctum::actingAs($this->staff);

        $this->putJson(route('v1.user.change.name'), ['name' => 'zidan'])
            ->assertStatus(200)
            ->assertJsonStructure([
                'message'
            ], [
                'message' => 'Name updated successfully.'
            ]);

        $this->assertEquals(null, Cache::get("staff_".$this->staff->id));
        $this->assertDatabaseHas('users', [
            'id' => $this->staff->id,
            'name' => 'zidan'
        ]);
    }

    public function test_update_name_as_admin_should_forget_cache_and_return_bool()
    {
        Sanctum::actingAs($this->admin);

        $this->putJson(route('v1.user.change.name'), ['name' => 'zidan'])
            ->assertStatus(200)
            ->assertJsonStructure([
                'message'
            ], [
                'message' => 'Name updated successfully.'
            ]);

        $this->assertEquals(null, Cache::get("admin_".$this->admin->id));
        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'name' => 'zidan'
        ]);
    }

    public function test_update_email_throws_InvalidCredentialException_with_invalid_password()
    {
        Sanctum::actingAs($this->admin);

        $this->putJson(route('v1.user.change.email'), [
            'new_email' => 'newmail@gmail.com',
            'password' => 'abcdef'
        ])
        ->assertStatus(401)
        ->assertJsonStructure([
            'message'
        ], [
            'message' => 'Wrong password.'
        ]);

        $this->assertDatabaseMissing('users', [
            'id' => $this->admin->id,
            'email' => 'newmail@example.com'
        ]);
    }

    public function test_update_email_as_staff_should_forget_cache_and_return_true()
    {
        Sanctum::actingAs($this->staff);

        $this->putJson(route('v1.user.change.email'), [
            'new_email' => 'newmail@gmail.com',
            'password' => 'staff12345'
        ])
        ->assertStatus(200)
        ->assertJsonStructure([
            'message'
        ], [
            'message' => 'Email updated successfully.'
        ]);

        $this->assertEquals(null, Cache::get("staff_".$this->staff->id));
        $this->assertDatabaseHas('users', [
            'id' => $this->staff->id,
            'email' => 'newmail@gmail.com'
        ]);
    }

    public function test_update_email_as_admin_should_forget_cache_and_return_true()
    {
        Sanctum::actingAs($this->admin);

        $this->putJson(route('v1.user.change.email'), [
            'new_email' => 'newmail@gmail.com',
            'password' => 'admin12345'
        ])
        ->assertStatus(200)
        ->assertJsonStructure([
            'message'
        ], [
            'message' => 'Email updated successfully.'
        ]);

        $this->assertEquals(null, Cache::get("admin_".$this->admin->id));
        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'email' => 'newmail@gmail.com'
        ]);
    }

    public function test_update_password_throws_InvalidCredentialException_with_invalid_password()
    {
        Sanctum::actingAs($this->admin);

        $this->putJson(route('v1.user.change.password'), [
            'new_password' => 'newPassword',
            'old_password' => 'cihuy'
        ])
        ->assertStatus(401)
        ->assertJsonStructure([
            'message'
        ], [
            'message' => 'Wrong password.'
        ]);

        $this->assertEquals($this->admin, Cache::get("admin_".$this->admin->id));
    }

    public function test_update_password_as_staff_should_forget_cache_and_return_true()
    {
        Sanctum::actingAs($this->staff);

        $this->putJson(route('v1.user.change.password'), [
            'new_password' => 'myNewPassword',
            'old_password' => 'staff12345'
        ])
        ->assertStatus(200)
        ->assertJsonStructure([
            'message'
        ], [
            'message' => 'Password updated successfully.'
        ]);

        $this->assertEquals(null, Cache::get("staff_".$this->staff->id));
    }

    public function test_update_password_as_admin_should_forget_cache_and_return_true()
    {
        Sanctum::actingAs($this->admin);

        $this->putJson(route('v1.user.change.password'), [
            'new_password' => 'myNewPassword',
            'old_password' => 'admin12345'
        ])
        ->assertStatus(200)
        ->assertJsonStructure([
            'message'
        ], [
            'message' => 'Password updated successfully.'
        ]);

        $this->assertEquals(null, Cache::get("admin_".$this->admin->id));
    }
}
