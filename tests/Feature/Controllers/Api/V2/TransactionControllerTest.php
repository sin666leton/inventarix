<?php

namespace Tests\Feature\Controllers\Api\V2;

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
            'password' => 'test'
        ]);

        $this->staff = User::create([
            'role_id' => $staff->id,
            'name' => 'staff',
            'email' => 'staff@example.com',
            'password' => 'test'
        ]);

        Cache::flush();
    }

    public function test_find_transaction_return_404Message_with_invalid_id()
    {        
        Sanctum::actingAs($this->admin, config('ability.admin'));

        $this->getJson(route('v2.transactions.show', ['transaction' => 999]))
            ->assertStatus(404)
            ->assertJsonStructure([
                'message'
            ], [
                'message' => 'Transaction Not Found.'
            ]);
    }

    public function test_find_transaction_as_admin_and_return_transaction()
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
            'user_id' => $this->staff->id,
            'item_id' => $item->id,
            'type' => 'out',
            'quantity' => 5,
            'description' => null
        ]);

        $this->getJson(route('v2.transactions.show', ['transaction' => $transaction->id]))
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user_id',
                    'item_id',
                    'type',
                    'quantity',
                    'description',
                    'user',
                    'item'
                ]
            ], [
                'data' => [
                    'user_id' => $this->staff->id,
                    'item_id' => $item->id,
                    'type' => 'out',
                    'quantity' => 5,
                    'description' => null,
                    'user' => [
                        'name' => $this->staff->name
                    ],
                    'item' => [
                        'name' => $item->name
                    ]
                ]
            ]);
    }

    public function test_find_transaction_as_staff_and_return_v1_transaction()
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

        $this->getJson(route('v2.transactions.show', ['transaction' => $transaction->id]))
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user_id',
                    'item_id',
                    'type',
                    'quantity',
                    'description',
                ]
            ], [
                'data' => [
                    'user_id' => $this->staff->id,
                    'item_id' => $item->id,
                    'type' => 'out',
                    'quantity' => 5,
                    'description' => null,
                ]
            ]);
    }
}
