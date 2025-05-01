<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Category;
use App\Models\Item;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ItemControllerTest extends TestCase
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

    public function test_index_item_throws_MissingParameterException()
    {
        Sanctum::actingAs($this->admin, config('ability.admin'));

        $this->getJson(route('v1.items.index'))
            ->assertStatus(422)
            ->assertJsonStructure([
                'message'
            ], [
                'message' => 'Missing required parameter: ?category'
            ]);
    }

    public function test_admin_can_access_index_method_and_return_paginate_items()
    {
        Sanctum::actingAs($this->admin, config('ability.admin'));

        $category = Category::create([
            'name' => 'test',
            'description' => null
        ]);

        $this->getJson(route('v1.items.index', ['category' => $category->id]))
            ->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'total'
            ]);
    }

    public function test_staff_can_access_index_method_and_return_paginate_items()
    {
        Sanctum::actingAs($this->staff, config('ability.staff'));

        $category = Category::create([
            'name' => 'test',
            'description' => null
        ]);

        $this->getJson(route('v1.items.index', ['category' => $category->id]))
            ->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'total'
            ]);
    }
    
    public function test_admin_can_create_item_and_return_item_json()
    {
        Sanctum::actingAs($this->admin, config('ability.admin'));

        $category = Category::create([
            'name' => 'test',
            'description' => null
        ]);

        $this->postJson(
            route('v1.items.store'),
            [
                    'category_id' => $category->id,
                    'name' => 'Pulpen',
                    'code' => '#PEN20',
                    'stock' => 20
                ]
            )
            ->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ], [
                'data' => [
                    'category_id' => $category->id,
                    'name' => 'Pulpen',
                    'code' => '#PEN20',
                    'stock' => 20
                ]
            ]);

        $this->assertDatabaseHas('items', [
            'category_id' => $category->id,
            'name' => 'Pulpen',
            'code' => '#PEN20',
            'stock' => 20
        ]);
    }

    public function test_staff_cant_create_item_and_return_403Forbidden()
    {
        Sanctum::actingAs($this->staff, config('ability.staff'));

        $category = Category::create([
            'name' => 'test',
            'description' => null
        ]);

        $this->postJson(
            route('v1.items.store'),
            [
                    'category_id' => $category->id,
                    'name' => 'Pensil',
                    'code' => '#PSL20',
                    'stock' => 20
                ]
            )
            ->assertStatus(403)
            ->assertJsonStructure([
                'message'
            ], [
                'message' => "You don't have permission for this action."
            ]);

        $this->assertDatabaseMissing('items', [
            'category_id' => $category->id,
            'name' => 'Pensil',
            'code' => '#PSL20',
            'stock' => 20
        ]);
    }

    public function test_update_item_return_404NotFound_with_invalid_id()
    {
        Sanctum::actingAs($this->admin, config('ability.admin'));

        $this->putJson(route('v1.items.update', ['item' => 999]), [
            'name' => 'eada',
            'code' => '#adwa',
            'stock' => 40
        ])
        ->assertStatus(404)
        ->assertJsonStructure([
            'message'
        ], [
            'message' => 'Item Not Found.'
        ]);
    }

    public function test_admin_can_update_item_and_return_item_json()
    {
        Sanctum::actingAs($this->admin, config('ability.admin'));

        $category = Category::create([
            'name' => 'test',
            'description' => null
        ]);

        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'Pulpenn',
            'code' => '#PLNN20',
            'stock' => 20
        ]);

        $this->putJson(route('v1.items.update', ['item' => $item->id]), [
            'name' => 'Laptop',
            'stock' => 40
        ])
        ->assertStatus(200)
        ->assertJsonStructure([
            'data'
        ], [
            'data' => [
                'id' => $item->id,
                'name' => 'Laptop',
                'code' => '#LPP40'
            ]
        ]);

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'category_id' => $category->id,
            'name' => 'Laptop',
            'stock' => 40
        ]);
    }

    public function test_delete_item_return_404NotFound_with_invalid_id()
    {
        Sanctum::actingAs($this->admin, config('ability.admin'));

        $this->deleteJson(route('v1.items.destroy', ['item' => 9999]))
            ->assertStatus(404)
            ->assertJsonStructure([
                'message'
            ], [
                'message' => 'Item Not Found.'
            ]);
    }

    public function test_staff_cant_update_item_and_return_403Forbidden()
    {
        Sanctum::actingAs($this->staff, config('ability.staff'));

        $category = Category::create([
            'name' => 'test',
            'description' => null
        ]);

        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'Pulpenn',
            'code' => '#PLNN20',
            'stock' => 20
        ]);

        $this->putJson(route('v1.items.update', ['item' => $item->id]), [
            'name' => 'abcd',
            'code' => '#plllaaw',
            'stock' => 1
        ])
        ->assertStatus(403)
        ->assertJsonStructure([
            'message'
        ], [
            'message' => "You don't have permission for this action."
        ]);

        $this->assertDatabaseMissing('items', [
            'id' => $item->id,
            'name' => 'abcd',
            'code' => '#plllaaw',
            'stock' => 1
        ]);
    }

    public function test_admin_can_delete_item_and_return_200()
    {
        Sanctum::actingAs($this->admin, config('ability.admin'));

        $category = Category::create([
            'name' => 'test',
            'description' => null
        ]);

        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'Pulpenn',
            'code' => '#PLNN20',
            'stock' => 20
        ]);

        $this->deleteJson(route('v1.items.destroy', ['item' => $item->id]))
            ->assertStatus(200)
            ->assertJsonStructure([
                'message'
            ], [
                'message' => 'Item has been successfully deleted.'
            ]);

        $this->assertDatabaseMissing('items', [
            'category_id' => $category->id,
            'id' => $item->id,
            'name' => 'Pulpenn',
            'code' => '#PLNN20',
            'stock' => 20
        ]);
    }
}
