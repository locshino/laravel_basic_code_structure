<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;

use App\Models\Product; // Import Product model
use App\Policies\ProductPolicy; // Import ProductPolicy
use App\Models\Category; // Import Category model
use App\Policies\CategoryPolicy; // Import CategoryPolicy (You need to create this)
use App\Models\User; // Import User model
use App\Policies\UserPolicy; // Import UserPolicy (You need to create this)


use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{

    protected $policies = [
        // Map the Product model to the ProductPolicy
        Product::class => ProductPolicy::class,

        // You will need to create and register policies for other models too
        Category::class => CategoryPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Implicitly grant "super admin" role all permissions
        // This is a common practice to give an admin role full access without assigning every single permission
        // Check if the authenticated user has the 'admin' role
        // Note: This check runs on every authorization check, be mindful of performance if you have many checks
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('admin')) {
                return true; // Admins can do anything
            }
        });

        // You can also define Gates here if not using Policies for certain actions
        // Gate::define('manage products', function (User $user) {
        //     return $user->hasPermissionTo('manage products');
        // });
    }
}
