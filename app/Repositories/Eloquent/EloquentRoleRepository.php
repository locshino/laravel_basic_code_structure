<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\RoleRepositoryInterface;
use Spatie\Permission\Models\Role; // Assuming Spatie package
use Illuminate\Database\Eloquent\Collection;

/**
 * Eloquent implementation of RoleRepositoryInterface (using Spatie).
 * Handles data access for Roles using Spatie's Role model.
 */
class EloquentRoleRepository implements RoleRepositoryInterface
{
    protected Role $roleModel;

    /**
     * Constructor.
     *
     * @param Role $roleModel The Role model instance (Spatie).
     */
    public function __construct(Role $roleModel)
    {
        $this->roleModel = $roleModel;
    }

    /**
     * Get all roles.
     *
     * @return Collection<int, Role>
     */
    public function getAll(): Collection
    {
        return $this->roleModel->all();
    }

    /**
     * Find a role by its ID.
     *
     * @param int $id The role ID.
     * @return Role|null The role model or null if not found.
     */
    public function findById(int $id): ?Role
    {
        return $this->roleModel->find($id);
    }

    /**
     * Find a role by its name.
     *
     * @param string $name The role name.
     * @return Role|null The role model or null if not found.
     */
    public function findByName(string $name): ?Role
    {
        return $this->roleModel->where('name', $name)->first();
    }
}
