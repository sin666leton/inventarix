<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Category;
use App\Models\Item;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
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
            ->create();

        $this->staff = User::factory()
            ->state(['role_id' => $staffRole->id])
            ->create();

        Cache::flush();
    }

    public function test_admin_can_access_index_method_and_return_paginate_transactions()
    {
        Sanctum::actingAs($this->admin, config('ability.admin'));

        $this->getJson(route('v1.transactions.index'))
            ->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'total'
            ]);
    }

    public function test_staff_can_access_index_method_and_return_paginate_transactions()
    {
        Sanctum::actingAs($this->staff, config('ability.staff'));

        $this->getJson(route('v1.transactions.index'))
            ->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'total'
            ]);
    }

    public function test_find_transaction_return_404NotFound_with_invalid_id()
    {
        Sanctum::actingAs($this->admin, config('ability.admin'));

        $this->getJson(route('v1.transactions.show', ['transaction' => 999]))
            ->assertStatus(404)
            ->assertJsonStructure([
                'message'
            ], [
                'message' => 'Transaction Not Found.'
            ]);
    }

    public function test_admin_can_find_transaction_and_return_transaction_json()
    {
        Sanctum::actingAs($this->admin, config('ability.admin'));

        $category = Category::create([
            'name' => 'test',
            'description' => null
        ]);

        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'test',
            'code' => '#TS10',
            'stock' => 15
        ]);

        $transaction = Transaction::create([
            'user_id' => $this->admin->id,
            'item_id' => $item->id,
            'type' => 'out',
            'quantity' => 5,
            'description' => null
        ]);

        $this->getJson(route('v1.transactions.show', ['transaction' => $transaction->id]))
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user_id',
                    'item_id',
                    'type',
                    'quantity',
                    'description'
                ]
            ], [
                'data' => [
                    'user_id' => $this->admin->id,
                    'item_id' => $item->id,
                    'type' => 'out',
                    'quantity' => 5,
                    'description' => null
                ]
            ]);
    }

    public function test_staff_can_find_transaction_and_return_transaction_json()
    {
        Sanctum::actingAs($this->staff, config('ability.staff'));

        $category = Category::create([
            'name' => 'test',
            'description' => null
        ]);

        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'test',
            'code' => '#TS10',
            'stock' => 15
        ]);

        $transaction = Transaction::create([
            'user_id' => $this->staff->id,
            'item_id' => $item->id,
            'type' => 'out',
            'quantity' => 5,
            'description' => null
        ]);

        $this->getJson(route('v1.transactions.show', ['transaction' => $transaction->id]))
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user_id',
                    'item_id',
                    'type',
                    'quantity',
                    'description'
                ]
            ], [
                'data' => [
                    'user_id' => $this->admin->id,
                    'item_id' => $item->id,
                    'type' => 'out',
                    'quantity' => 5,
                    'description' => null
                ]
            ]);
    }

    public function test_admin_can_create_transaction_type_in_and_return_transaction_json()
    {
        Sanctum::actingAs($this->admin, config('ability.admin'));

        $category = Category::create([
            'name' => 'test',
            'description' => null
        ]);

        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'test',
            'code' => '#TS10',
            'stock' => 10
        ]);

        $this->postJson(route('v1.transactions.store'), [
            'item_id' => $item->id,
            'user_id' => $this->admin->id,
            'type' => 'in',
            'quantity' => 5,
            'description' => null
        ])
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'user_id',
                'item_id',
                'type',
                'quantity',
                'description'
            ]
        ], [
            'data' => [
                'user_id' => $this->admin->id,
                'item_id' => $item->id,
                'type' => 'in',
                'quantity' => 5,
                'description' => null 
            ]
        ]);

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'stock' => 15
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->admin->id,
            'item_id' => $item->id,
            'type' => 'in',
            'quantity' => 5,
            'description' => null 
        ]);
    }

    public function test_staff_can_create_transaction_type_in_and_return_transaction_json()
    {
        Sanctum::actingAs($this->staff, config('ability.staff'));

        $category = Category::create([
            'name' => 'test',
            'description' => null
        ]);

        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'test',
            'code' => '#TS10',
            'stock' => 10
        ]);

        $this->postJson(route('v1.transactions.store'), [
            'item_id' => $item->id,
            'user_id' => $this->staff->id,
            'type' => 'in',
            'quantity' => 5,
            'description' => null
        ])
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'user_id',
                'item_id',
                'type',
                'quantity',
                'description'
            ]
        ], [
            'data' => [
                'user_id' => $this->staff->id,
                'item_id' => $item->id,
                'type' => 'in',
                'quantity' => 5,
                'description' => null 
            ]
        ]);

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'stock' => 15
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->staff->id,
            'item_id' => $item->id,
            'type' => 'in',
            'quantity' => 5,
            'description' => null 
        ]);
    }

    public function test_admin_can_create_transaction_type_out_and_return_transaction_json()
    {
        Sanctum::actingAs($this->admin, config('ability.admin'));

        $category = Category::create([
            'name' => 'test',
            'description' => null
        ]);

        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'test',
            'code' => '#TS10',
            'stock' => 10
        ]);

        $this->postJson(route('v1.transactions.store'), [
            'item_id' => $item->id,
            'user_id' => $this->admin->id,
            'type' => 'out',
            'quantity' => 5,
            'description' => null
        ])
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'user_id',
                'item_id',
                'type',
                'quantity',
                'description'
            ]
        ], [
            'data' => [
                'user_id' => $this->admin->id,
                'item_id' => $item->id,
                'type' => 'out',
                'quantity' => 5,
                'description' => null 
            ]
        ]);

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'stock' => 5
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->admin->id,
            'item_id' => $item->id,
            'type' => 'out',
            'quantity' => 5,
            'description' => null 
        ]);
    }

    public function test_staff_can_create_transaction_type_out_and_return_transaction_json()
    {
        Sanctum::actingAs($this->staff, config('ability.staff'));

        $category = Category::create([
            'name' => 'test',
            'description' => null
        ]);

        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'test',
            'code' => '#TS10',
            'stock' => 10
        ]);

        $this->postJson(route('v1.transactions.store'), [
            'item_id' => $item->id,
            'user_id' => $this->staff->id,
            'type' => 'out',
            'quantity' => 5,
            'description' => null
        ])
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'user_id',
                'item_id',
                'type',
                'quantity',
                'description'
            ]
        ], [
            'data' => [
                'user_id' => $this->staff->id,
                'item_id' => $item->id,
                'type' => 'out',
                'quantity' => 5,
                'description' => null 
            ]
        ]);

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'stock' => 5
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->staff->id,
            'item_id' => $item->id,
            'type' => 'out',
            'quantity' => 5,
            'description' => null 
        ]);
    }

    public function test_staff_cant_delete_transaction_return_403Forbidden()
    {
        Sanctum::actingAs($this->staff, config('ability.staff'));

        $this->deleteJson(route('v1.transactions.destroy', ['transaction' => 1]))
            ->assertStatus(403)
            ->assertJsonStructure([
                'message'
            ], [
                'message' => "You don't have permission for this action."
            ]);
    }

    public function test_admin_can_delete_transaction_type_in_and_return_transaction_json()
    {
        Sanctum::actingAs($this->admin, config('ability.admin'));

        $category = Category::create([
            'name' => 'test',
            'description' => null
        ]);

        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'test',
            'code' => '#TS10',
            'stock' => 15
        ]);

        $transaction = Transaction::create([
            'user_id' => $this->admin->id,
            'item_id' => $item->id,
            'type' => 'in',
            'quantity' => 5,
            'description' => null
        ]);

        $this->deleteJson(route('v1.transactions.destroy', ['transaction' => $transaction->id]))
            ->assertStatus(200)
            ->assertJsonStructure([
                'message'
            ], [
                'message' => 'Transaction has been successully deleted.'
            ]);

        $this->assertDatabaseMissing('transactions', [
            'user_id' => $this->admin->id,
            'item_id' => $item->id,
            'type' => 'in',
            'quantity' => 5,
            'description' => null
        ]);

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'category_id' => $category->id,
            'name' => 'test',
            'code' => '#TS10',
            'stock' => 10
        ]);
    }

    public function test_admin_can_delete_transaction_type_out_and_return_transaction_json()
    {
        Sanctum::actingAs($this->admin, config('ability.admin'));

        $category = Category::create([
            'name' => 'test',
            'description' => null
        ]);

        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'test',
            'code' => '#TS10',
            'stock' => 15
        ]);

        $transaction = Transaction::create([
            'user_id' => $this->admin->id,
            'item_id' => $item->id,
            'type' => 'out',
            'quantity' => 5,
            'description' => null
        ]);

        $this->deleteJson(route('v1.transactions.destroy', ['transaction' => $transaction->id]))
            ->assertStatus(200)
            ->assertJsonStructure([
                'message'
            ], [
                'message' => 'Transaction has been successully deleted.'
            ]);

        $this->assertDatabaseMissing('transactions', [
            'user_id' => $this->admin->id,
            'item_id' => $item->id,
            'type' => 'out',
            'quantity' => 5,
            'description' => null
        ]);

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'category_id' => $category->id,
            'name' => 'test',
            'code' => '#TS10',
            'stock' => 20
        ]);
    }
}
