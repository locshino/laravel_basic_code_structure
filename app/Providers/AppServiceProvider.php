<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\Repositories\ProductRepositoryInterface;
use App\Repositories\Eloquent\EloquentProductRepository;
use App\Interfaces\Repositories\CategoryRepositoryInterface;
use App\Repositories\Eloquent\EloquentCategoryRepository;
use App\Interfaces\Repositories\UserRepositoryInterface;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Interfaces\Repositories\RoleRepositoryInterface;
use App\Repositories\Eloquent\EloquentRoleRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, EloquentRoleRepository::class);

        // If you define Service Interfaces, you would bind them here too
        // $this->app->bind(ProductServiceInterface::class, ProductService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
