<?php

namespace App\Interfaces\Repositories;

use Spatie\Permission\Models\Role; // Assuming Spatie package
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface for Role Repository (if using Spatie or custom roles).
 * Defines the contract for data access operations for Roles.
 */
interface RoleRepositoryInterface
{
    /**
     * Get all roles.
     *
     * @return Collection<int, Role>
     */
    public function getAll(): Collection;

    /**
     * Find a role by its ID.
     *
     * @param int $id The role ID.
     * @return Role|null The role model or null if not found.
     */
    public function findById(int $id): ?Role;

    /**
     * Find a role by its name.
     *
     * @param string $name The role name.
     * @return Role|null The role model or null if not found.
     */
    public function findByName(string $name): ?Role;

    // Add methods for managing permissions if needed
}
