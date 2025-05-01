<?php

namespace App\Providers;

use App\Contracts\Auth;
use App\Contracts\Category;
use App\Contracts\Item;
use App\Contracts\Staff;
use App\Contracts\Transaction;
use App\Contracts\User;
use App\Policies\CategoryPolicy;
use App\Policies\ItemPolicy;
use App\Policies\TransactionPolicy;
use App\Policies\UserPolicy;
use App\Repositories\AuthRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\ItemRepository;
use App\Repositories\StaffRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        \App\Models\Category::class => CategoryPolicy::class,
        \App\Models\Item::class => ItemPolicy::class,
        \App\Models\Transaction::class => TransactionPolicy::class,
        \App\Models\User::class => UserPolicy::class
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(Category::class, CategoryRepository::class);
        $this->app->bind(Item::class, ItemRepository::class);
        $this->app->bind(Transaction::class, TransactionRepository::class);
        $this->app->bind(Auth::class, AuthRepository::class);
        $this->app->bind(Staff::class, StaffRepository::class);
        $this->app->bind(User::class, UserRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
