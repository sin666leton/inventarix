<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Exceptions\ActionForbiddenException;
use App\Exceptions\BadRequestException;
use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Http\Controllers\api\V1\CategoryController;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
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

    public function test_admin_can_access_index_method_and_return_paginate_category()
    {
        Sanctum::actingAs($this->admin, config('ability.admin'));

        $response = $this->getJson(route('v1.categories.index'));
    
        $response->assertStatus(200);
    }

    public function test_staff_can_access_index_method_and_return_paginate_category()
    {
        Sanctum::actingAs($this->staff, config('ability.staff'));

        $response = $this->getJson(route('v1.categories.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_find_category_and_return_category()
    {
        Sanctum::actingAs($this->admin, config('ability.admin'));

        $category = Category::create([
            'name' => 'Perabotan',
            'description' => 'Barang-barang kebutuhan rumah tangga'
        ]);

        $this->getJson(route('v1.categories.show', ['category' => $category->id]))
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'name',
                    'description'
                ]
            ], [
                'data' => [
                    'name' => 'Perabotan',
                    'description' => 'Barang-barang kebutuhan rumah tangga'
                ]
            ]);
    }

    public function test_staff_can_find_category_and_return_category()
    {
        Sanctum::actingAs($this->staff, config('ability.staff'));

        $category = Category::create([
            'name' => 'Perabotan',
            'description' => 'Barang-barang kebutuhan rumah tangga'
        ]);

        $this->getJson(route('v1.categories.show', ['category' => $category->id]))
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'name',
                    'description'
                ]
            ], [
                'data' => [
                    'name' => 'Perabotan',
                    'description' => 'Barang-barang kebutuhan rumah tangga'
                ]
            ]);
    }

    public function test_admin_can_create_category_and_return_category_json()
    {
        Sanctum::actingAs($this->admin, config('ability.admin'));

        $response = $this->postJson(route('v1.categories.store'), [
            'name' => 'Flash sale'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'name'
                ]
            ], [
                'data' => [
                    'name' => 'Flash sale'
                ]
            ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Flash sale'
        ]);
    }

    public function test_staff_cant_create_category_and_return_403Forbidden_json()
    {
        Sanctum::actingAs($this->staff, config('ability.staff'));

        $response = $this->postJson(route('v1.categories.store'), [
            'name' => 'Staff items'
        ]);
        $response->assertStatus(403)
            ->assertJsonStructure([
                'message'
            ], [
                'message'  => "You don't have permission for this action."
            ]);

        $this->assertDatabaseMissing('categories', [
            'name' => 'Staff items'
        ]);
    }

    public function test_update_category_return_404NotFound_when_id_invalid()
    {
        Sanctum::actingAs($this->admin, config('ability.admin'));

        $id = 999;

        $this->putJson(
            route('v1.categories.update', ['category' => $id]),
            [
                'name' => 'SOMENAME'
            ]
        )
        ->assertStatus(404)
        ->assertJsonStructure([
            'message'
        ], [
            'message' => 'Category Not Found.'
        ]);
    }

    public function test_admin_can_update_category_and_return_category_json()
    {
        $category = Category::create([
            'name' => 'Test'
        ]);

        Sanctum::actingAs($this->admin, config('ability.admin'));

        $response = $this->putJson(
            route('v1.categories.update', [
                'category' => $category->id
            ]),
            [
                'name' => 'Elektronik'
            ]
        );
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'name'
                ]
            ], [
                'data' => [
                    'name' => 'Elektronik'
                ]
            ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Elektronik'
        ]);
    }
    
    public function test_staff_cant_update_category_and_return_403Forbidden_json()
    {
        $category = Category::create([
            'name' => 'Test'
        ]);

        Sanctum::actingAs($this->staff, config('ability.staff'));

        $response = $this->putJson(
            route('v1.categories.update', [
                'category' => $category->id
            ]),
            [
                'name' => 'Elektronik'
            ]
        );
        
        $response->assertStatus(403)
            ->assertJsonStructure([
                'message'
            ], [
                'message' => "You don't have permission for this action."
            ]);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
            'name' => 'Elektronik'
        ]);
    }

    public function test_delete_category_return_404NotFound_with_invalid_id()
    {
        Sanctum::actingAs($this->admin, config('ability.admin'));

        $this->deleteJson(route('v1.categories.destroy', ['category' => 999]))
            ->assertStatus(404)
            ->assertJsonStructure([
                'message'
            ], [
                'message' => 'Category Not Found.'
            ]);
    }

    public function test_admin_can_delete_category()
    {
        Sanctum::actingAs($this->admin, config('ability.admin'));

        $category = Category::create([
            'name' => 'testing',
            'description' => 'testing description'
        ]);

        $this->deleteJson(route('v1.categories.destroy', ['category' => $category]))
            ->assertStatus(200)
            ->assertJsonStructure([
                'message'
            ], [
                'message' => 'Category has been successfully deleted.'
            ]);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
            'name' => 'testing'
        ]);
    }

    public function test_staff_cant_delete_category_and_return_403Forbidden_json()
    {
        Sanctum::actingAs($this->staff, config('ability.staff'));

        $category = Category::create([
            'name' => 'testing',
            'description' => 'testing description'
        ]);

        $this->deleteJson(route('v1.categories.destroy', ['category' => $category]))
            ->assertStatus(403)
            ->assertJsonStructure([
                'message'
            ], [
                'message' => "You don't have permission for this action."
            ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'testing',
            'description' => 'testing description'
        ]);
    }
}
