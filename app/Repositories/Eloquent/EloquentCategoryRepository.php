<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

/**
 * Eloquent implementation of CategoryRepositoryInterface.
 * Handles data access for Categories using Eloquent ORM.
 */
class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    protected Category $categoryModel;

    /**
     * Constructor.
     *
     * @param Category $categoryModel The Category model instance.
     */
    public function __construct(Category $categoryModel)
    {
        $this->categoryModel = $categoryModel;
    }

    /**
     * Get all categories.
     *
     * @return Collection<int, Category>
     */
    public function getAll(): Collection
    {
        return $this->categoryModel->all();
    }

    /**
     * Find a category by its ID.
     *
     * @param int $id The category ID.
     * @return Category|null The category model or null if not found.
     */
    public function findById(int $id): ?Category
    {
        return $this->categoryModel->where('id', $id)->first();
    }

    /**
     * Create a new category.
     *
     * @param array $data The category data.
     * @return Category The created category model.
     */
    public function create(array $data): Category
    {
        return $this->categoryModel->create($data);
    }

    /**
     * Update a category by its ID.
     *
     * @param int $id The category ID.
     * @param array $data The data to update.
     * @return Category|null The updated category model or null if not found.
     */
    public function update(int $id, array $data): ?Category
    {
        $category = $this->findById($id);
        if (!$category) {
            return null; // Service layer will handle CategoryNotFoundException
        }
        $category->update($data);
        return $category;
    }

    /**
     * Delete a category by its ID.
     *
     * @param int $id The category ID.
     * @return bool True on success, false on failure or not found.
     */
    public function delete(int $id): bool
    {
        $category = $this->findById($id);
        if (!$category) {
            return false; // Service layer will handle CategoryNotFoundException
        }
        return $category->delete();
    }
}
