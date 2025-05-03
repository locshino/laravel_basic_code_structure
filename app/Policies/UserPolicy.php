<?php

namespace App\Policies;

use App\Models\User; // Import User model
use Illuminate\Auth\Access\Response; // Import Response for more detailed authorization responses

class UserPolicy
{
    /**
     * Determine whether the user can view any users.
     * Corresponds to @can('viewAny', App\Models\User::class)
     * Logic: Only users with 'manage users' permission can view the user list.
     */
    public function viewAny(User $user): bool
    {
        // User must have the 'manage users' permission to view the list
        return $user->can('manage users'); // Uses Spatie's can() method
    }

    /**
     * Determine whether the user can view the user.
     * Corresponds to @can('view', $userModel)
     * Logic: User must have 'manage users' permission, or be viewing their own profile.
     */
    public function view(User $user, User $userModel): bool
    {
        // User must have the 'manage users' permission OR be viewing their own profile
        return $user->can('manage users') || $user->id === $userModel->id; // Uses Spatie's can() method
    }

    /**
     * Determine whether the user can create users.
     * Corresponds to @can('create', App\Models\User::class)
     * Logic: User must have the 'create users' permission.
     */
    public function create(User $user): bool
    {
        // User must have the 'create users' permission
        return $user->can('create users'); // Uses Spatie's can() method
    }

    /**
     * Determine whether the user can update the user.
     * Corresponds to @can('update', $userModel)
     * Logic: User must have 'manage users' permission, or be updating their own profile.
     */
    public function update(User $user, User $userModel): bool
    {
        // User must have the 'manage users' permission OR be updating their own profile
        return $user->can('manage users') || $user->id === $userModel->id; // Uses Spatie's can() method
    }

    /**
     * Determine whether the user can delete the user.
     * Corresponds to @can('delete', $userModel)
     * Logic: User must have 'manage users' permission, and cannot delete themselves.
     */
    public function delete(User $user, User $userModel): bool
    {
        // User must have the 'manage users' permission AND cannot delete their own account
        return $user->can('manage users') && $user->id !== $userModel->id; // Uses Spatie's can() method
    }

    /**
     * Determine whether the user can assign roles to the user.
     * Corresponds to @can('assignRole', $userModel)
     * Logic: User must have 'assign roles' permission, and cannot assign roles to themselves (optional).
     */
    public function assignRole(User $user, User $userModel): bool
    {
        // User must have the 'assign roles' permission
        // Optional: Prevent assigning roles to themselves: && $user->id !== $userModel->id;
        return $user->can('assign roles'); // Uses Spatie's can() method
    }


    // Optional: Add other policy methods if needed
    // public function restore(User $user, User $userModel): bool { ... }
    // public function forceDelete(User $user, User $userModel): bool { ... }
}
