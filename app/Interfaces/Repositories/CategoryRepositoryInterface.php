<?php

namespace App\Interfaces\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface for Category Repository.
 * Defines the contract for data access operations for Categories.
 */
interface CategoryRepositoryInterface
{
    /**
     * Get all categories.
     *
     * @return Collection<int, Category>
     */
    public function getAll(): Collection;

    /**
     * Find a category by its ID.
     *
     * @param int $id The category ID.
     * @return Category|null The category model or null if not found.
     */
    public function findById(int $id): ?Category;

    /**
     * Create a new category.
     *
     * @param array $data The category data.
     * @return Category The created category model.
     */
    public function create(array $data): Category;

    /**
     * Update a category by its ID.
     *
     * @param int $id The category ID.
     * @param array $data The data to update.
     * @return Category|null The updated category model or null if not found.
     */
    public function update(int $id, array $data): ?Category;

    /**
     * Delete a category by its ID.
     *
     * @param int $id The category ID.
     * @return bool True on success, false on failure or not found.
     */
    public function delete(int $id): bool;
}
