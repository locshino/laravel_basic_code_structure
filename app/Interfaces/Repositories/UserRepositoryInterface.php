<?php

namespace App\Interfaces\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface for User Repository.
 * Defines the contract for data access operations for Users.
 */
interface UserRepositoryInterface
{
    /**
     * Get all users.
     *
     * @return Collection<int, User>
     */
    public function getAll(): Collection;

    /**
     * Find a user by their ID.
     *
     * @param int $id The user ID.
     * @return User|null The user model or null if not found.
     */
    public function findById(int $id): ?User;

    /**
     * Find a user by their email.
     *
     * @param string $email The user email.
     * @return User|null The user model or null if not found.
     */
    public function findByEmail(string $email): ?User;

    /**
     * Create a new user.
     *
     * @param array $data The user data.
     * @return User The created user model.
     */
    public function create(array $data): User;

    /**
     * Update a user by their ID.
     *
     * @param int $id The user ID.
     * @param array $data The data to update.
     * @return User|null The updated user model or null if not found.
     */
    public function update(int $id, array $data): ?User;

    /**
     * Delete a user by their ID.
     *
     * @param int $id The user ID.
     * @return bool True on success, false on failure or not found.
     */
    public function delete(int $id): bool;
}
