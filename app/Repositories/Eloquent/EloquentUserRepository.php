<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash; // To hash passwords

/**
 * Eloquent implementation of UserRepositoryInterface.
 * Handles data access for Users using Eloquent ORM.
 */
class EloquentUserRepository implements UserRepositoryInterface
{
    protected User $userModel;

    /**
     * Constructor.
     *
     * @param User $userModel The User model instance.
     */
    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }

    /**
     * Get all users.
     *
     * @return Collection<int, User>
     */
    public function getAll(): Collection
    {
        return $this->userModel->all();
    }

    /**
     * Find a user by their ID.
     *
     * @param int $id The user ID.
     * @return User|null The user model or null if not found.
     */
    public function findById(int $id): ?User
    {
        return $this->userModel->where('id', $id)->first();
    }

    /**
     * Find a user by their email.
     *
     * @param string $email The user email.
     * @return User|null The user model or null if not found.
     */
    public function findByEmail(string $email): ?User
    {
        return $this->userModel->where('email', $email)->first();
    }

    /**
     * Create a new user.
     *
     * @param array $data The user data.
     * @return User The created user model.
     */
    public function create(array $data): User
    {
        // Hash password before creating
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        return $this->userModel->create($data);
    }

    /**
     * Update a user by their ID.
     *
     * @param int $id The user ID.
     * @param array $data The data to update.
     * @return User|null The updated user model or null if not found.
     */
    public function update(int $id, array $data): ?User
    {
        $user = $this->findById($id);
        if (!$user) {
            return null; // Service layer will handle UserNotFoundException
        }
        // Hash password if it's being updated
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        $user->update($data);
        return $user;
    }

    /**
     * Delete a user by their ID.
     *
     * @param int $id The user ID.
     * @return bool True on success, false on failure or not found.
     */
    public function delete(int $id): bool
    {
        $user = $this->findById($id);
        if (!$user) {
            return false; // Service layer will handle UserNotFoundException
        }
        return $user->delete();
    }
}
