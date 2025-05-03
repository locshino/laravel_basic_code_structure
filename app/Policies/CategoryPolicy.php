<?php

namespace App\Policies;

use App\Models\User; // Import User model
use App\Models\Category; // Import Category model
use Illuminate\Auth\Access\Response; // Import Response for more detailed authorization responses

class CategoryPolicy
{
    /**
     * Determine whether the user can view any categories.
     * Corresponds to @can('viewAny', App\Models\Category::class)
     * Logic: Any user who can manage categories can view the list.
     */
    public function viewAny(User $user): bool
    {
        // User must have the 'manage categories' permission to view the list
        return $user->can('manage categories'); // Uses Spatie's can() method
    }

    /**
     * Determine whether the user can view the category.
     * Corresponds to @can('view', $category)
     * Logic: Any user who can manage categories can view a specific category.
     */
    public function view(User $user, Category $category): bool
    {
        // User must have the 'manage categories' permission to view a specific category
        return $user->can('manage categories'); // Uses Spatie's can() method
    }

    /**
     * Determine whether the user can create categories.
     * Corresponds to @can('create', App\Models\Category::class)
     * Logic: User must have the 'create categories' permission.
     */
    public function create(User $user): bool
    {
        // User must have the 'create categories' permission
        return $user->can('create categories'); // Uses Spatie's can() method
    }

    /**
     * Determine whether the user can update the category.
     * Corresponds to @can('update', $category)
     * Logic: User must have the 'update categories' permission.
     */
    public function update(User $user, Category $category): bool
    {
        // User must have the 'update categories' permission
        return $user->can('update categories'); // Uses Spatie's can() method
    }

    /**
     * Determine whether the user can delete the category.
     * Corresponds to @can('delete', $category)
     * Logic: User must have the 'delete categories' permission.
     */
    public function delete(User $user, Category $category): bool
    {
        // User must have the 'delete categories' permission
        return $user->can('delete categories'); // Uses Spatie's can() method
    }

    // Optional: Add other policy methods if needed
    // public function restore(User $user, Category $category): bool { ... }
    // public function forceDelete(User $user, Category $category): bool { ... }
}
