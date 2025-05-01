<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StaffControllerTest extends TestCase
{
    protected $admin;

    protected $staff;

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create([
            'name' => 'admin'
        ]);

        $staffRole = Role::create([
            'name' => 'staff'
        ]);

        $this->admin = User::factory()
            ->state(['role_id' => $adminRole->id])
            ->make();

        $this->staff = User::factory()
            ->state(['role_id' => $staffRole->id])
            ->make();

        Cache::flush();
    }

    public function test_staff_cant_paginate_staff_and_return_403Forbidden()
    {
        Sanctum::actingAs($this->staff, config('ability.staff'));

        $this->getJson(route('v1.staff.index'))
            ->assertStatus(403)
            ->assertJsonStructure([
                'message'
            ], [
                'message' => "You don't have permission for this action."
            ]);
    }

    public function test_admin_can_paginate_staff_and_return_staff_list()
    {
        Sanctum::actingAs($this->admin, config('ability.admin'));

        $this->getJson(route('v1.staff.index'))
            ->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'total'
            ]);
    }

    public function test_staff_cant_create_staff_and_return_403Forbidden()
    {
        Sanctum::actingAs($this->staff, config('ability.staff'));

        $this->postJson(route('v1.staff.store'), [
            'name' => 'new staff',
            'email' => 'new.staff@gmail.com',
            'password' => 'test'
        ])
        ->assertStatus(403)
        ->assertJsonStructure([
            'message'
        ], [
            'message' => "You don't have permission for this action"
        ]);

        $this->assertDatabaseMissing('users', [
            'name' => 'new staff',
            'email' => 'new.staff@gmail.com'
        ]);
    }

    public function test_admin_can_create_staff_and_return_staff()
    {
        Sanctum::actingAs($this->admin, config('ability.admin'));

        $this->postJson(route('v1.staff.store'), [
            'name' => 'new staff',
            'email' => 'new.staff@gmail.com',
            'password' => 'test123456'
        ])
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'name',
                'email'
            ]
        ], [
            'data' => [
                'name' => 'new staff',
                'email' => 'new.staff@gmail.com'
            ]
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'new staff',
            'email' => 'new.staff@gmail.com'
        ]);
    }

    public function test_staff_cant_update_staff_and_return_403Forbidden()
    {
        $anotherStaff = User::create([
            'role_id' => $this->staff->role_id,
            'name' => 'Zid',
            'email' => 'zid@gmail.com',
            'password' => Hash::make('123')
        ]);

        Sanctum::actingAs($this->staff, config('ability.staff'));

        $this->putJson(route('v1.staff.update', ['staff' => $anotherStaff->id]), [
            'name' => 'uca',
            'email' => 'uca@gmail.com'
        ])
        ->assertStatus(403)
        ->assertJsonStructure([
            'message'
        ], [
            'message' => "You don't have permission for this action."
        ]);

        $this->assertDatabaseMissing('users', [
            'name' => 'uca',
            'email' => 'uca@gmail.com'
        ]);
    }

    public function test_admin_can_update_staff_with_invalid_id_and_return_404()
    {
        User::create([
            'role_id' => $this->staff->role_id,
            'name' => 'Zid',
            'email' => 'zid@gmail.com',
            'password' => Hash::make('123')
        ]);

        Sanctum::actingAs($this->admin, config('ability.admin'));

        $this->deleteJson(route('v1.staff.destroy', ['staff' => 999]))
            ->assertStatus(404);
    }

    public function test_admin_can_update_staff_and_return_staff()
    {
        $anotherStaff = User::create([
            'role_id' => $this->staff->role_id,
            'name' => 'Zid',
            'email' => 'zid@gmail.com',
            'password' => Hash::make('123')
        ]);

        Sanctum::actingAs($this->admin, config('ability.admin'));

        $this->putJson(route('v1.staff.update', ['staff' => $anotherStaff->id]), [
            'name' => 'uca',
            'email' => 'uca@gmail.com'
        ])
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'name',
                'email'
            ]
        ], [
            'data' => [
                'name' => 'uca',
                'email' => 'uca@gmail.com'
            ]
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'uca',
            'email' => 'uca@gmail.com'
        ]);
    }

    public function test_admin_can_update_staff_with_same_email_and_return_staff()
    {
        $anotherStaff = User::create([
            'role_id' => $this->staff->role_id,
            'name' => 'Zid',
            'email' => 'zid@gmail.com',
            'password' => Hash::make('123')
        ]);

        Sanctum::actingAs($this->admin, config('ability.admin'));

        $this->putJson(route('v1.staff.update', ['staff' => $anotherStaff->id]), [
            'name' => 'uca',
            'email' => 'zid@gmail.com'
        ])
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'name',
                'email'
            ]
        ], [
            'data' => [
                'name' => 'uca',
                'email' => 'zid@gmail.com'
            ]
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'uca',
            'email' => 'zid@gmail.com'
        ]);
    }

    public function test_staff_cant_delete_staff_return_403Forbidden()
    {
        $anotherStaff = User::create([
            'role_id' => $this->staff->role_id,
            'name' => 'Zid',
            'email' => 'zid@gmail.com',
            'password' => Hash::make('123')
        ]);

        Sanctum::actingAs($this->staff, config('ability.staff'));

        $this->deleteJson(route('v1.staff.destroy', ['staff' => $anotherStaff->id]))
            ->assertStatus(403)
            ->assertJsonStructure([
                'message'
            ], [
                'message' => "You don't have permission for this action."
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Zid',
            'email' => 'zid@gmail.com'
        ]);
    }

    public function test_admin_can_delete_staff_with_invalid_id_and_return_404()
    {
        Sanctum::actingAs($this->admin, config('ability.admin'));

        $this->deleteJson(route('v1.staff.destroy', ['staff' => 999]))
            ->assertStatus(404)
            ->assertJsonStructure([
                'message'
            ], [
                'message' => 'User Not Found.'
            ]);
    }

    public function test_admin_can_delete_staff_return_success()
    {
        $anotherStaff = User::create([
            'role_id' => $this->staff->role_id,
            'name' => 'Zid',
            'email' => 'zid@gmail.com',
            'password' => Hash::make('123')
        ]);

        Sanctum::actingAs($this->admin, config('ability.admin'));

        $this->deleteJson(route('v1.staff.destroy', ['staff' => $anotherStaff->id]))
            ->assertStatus(200)
            ->assertJsonStructure([
                'message'
            ], [
                'message' => "Staff has been deleted successfully."
            ]);

        $this->assertDatabaseMissing('users', [
            'name' => 'Zid',
            'email' => 'zid@gmail.com'
        ]);
    }
}
