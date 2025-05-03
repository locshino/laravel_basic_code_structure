<?php

namespace App\Policies;

use App\Models\User; // Import User model
use App\Models\Product; // Import Product model
use Illuminate\Auth\Access\Response; // Import Response for more detailed authorization responses

class ProductPolicy
{
    /**
     * Determine whether the user can view any models.
     * (Optional: Can be used for index page visibility)
     */
    public function viewAny(User $user): bool
    {
        // Any authenticated user can view the product list
        // Guest access is handled by the Service layer filtering active products
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * (Optional: Can be used for show page visibility)
     */
    public function view(User $user, Product $product): bool
    {
        // Authenticated users can view any product they have permission for
        // Guests can only view active products (logic might be in Service or here)
        // Example: return $user->can('view products') || ($product->is_active && !Auth::check());
        return true; // For simplicity, assume any authenticated user can view once they reach this policy check
    }

    /**
     * Determine whether the user can create models.
     * Corresponds to @can('create', App\Models\Product::class) or Gate::allows('create', Product::class)
     */
    public function create(User $user): bool
    {
        // User must have the 'create products' permission
        return $user->can('create products'); // Uses Spatie's can() method
    }

    /**
     * Determine whether the user can update the model.
     * Corresponds to @can('update', $product) or Gate::allows('update', $user, $product)
     */
    public function update(User $user, Product $product): bool
    {
        // User must have the 'update products' permission
        return $user->can('update products'); // Uses Spatie's can() method

        // Optional: Add more complex logic here, e.g., only update their own products
        // return $user->can('update products') && $user->id === $product->user_id; // Assuming product has user_id
    }

    /**
     * Determine whether the user can delete the model.
     * Corresponds to @can('delete', $product) or Gate::allows('delete', $user, $product)
     */
    public function delete(User $user, Product $product): bool
    {
        // User must have the 'delete products' permission
        return $user->can('delete products'); // Uses Spatie's can() method

        // Optional: Add more complex logic here
        // return $user->can('delete products') && $user->id === $product->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     * (If using soft deletes)
     */
    // public function restore(User $user, Product $product): bool
    // {
    //     //
    // }

    /**
     * Determine whether the user can permanently delete the model.
     * (If using soft deletes)
     */
    // public function forceDelete(User $user, Product $product): bool
    // {
    //     //
    // }

    // Optional: Add a 'manage' method if you use @can('manage', $product)
    // public function manage(User $user, Product $product): bool
    // {
    //     return $user->can('manage products'); // Example
    // }
}
