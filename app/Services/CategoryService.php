<?php

namespace App\Services;

use App\Interfaces\Repositories\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use App\Exceptions\CategoryNotFoundException;
use Illuminate\Support\Str; // For slug generation
use Illuminate\Support\Facades\Log; // For logging errors

/**
 * Service layer for Category related business logic.
 * Handles business rules and orchestrates data access via Repository.
 */
class CategoryService
{
    protected CategoryRepositoryInterface $categoryRepository;

    /**
     * Constructor.
     *
     * @param CategoryRepositoryInterface $categoryRepository The category repository interface.
     */
    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Get all categories.
     *
     * @return Collection<int, Category>
     */
    public function getAllCategories(): Collection
    {
        return $this->categoryRepository->getAll();
    }

    /**
     * Get a specific category by ID.
     *
     * @param int $id The category ID.
     * @return Category
     * @throws CategoryNotFoundException If the category is not found.
     */
    public function getCategoryById(int $id): Category
    {
        $category = $this->categoryRepository->findById($id);

        if (!$category) {
            throw new CategoryNotFoundException("Category with ID {$id} not found.");
        }

        return $category;
    }

    /**
     * Create a new category.
     * Business logic: Generate slug.
     *
     * @param array $data Category data.
     * @return Category The created category.
     * @throws \Exception On repository creation failure.
     */
    public function createCategory(array $data): Category
    {
        // --- Business Logic ---

        // 1. Generate slug if not provided
        if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
            // Optional: Add logic to ensure slug uniqueness before saving
        }

        // --- Call Repository to persist data ---
        try {
            return $this->categoryRepository->create($data);
        } catch (\Exception $e) {
            Log::error("CategoryService failed to create category: " . $e->getMessage(), ['data' => $data]);
            throw new \Exception("Failed to create category.", 500, $e);
        }
    }

    /**
     * Update a category.
     * Business logic: Regenerate slug if name changes.
     *
     * @param int $id The category ID.
     * @param array $data Data to update.
     * @return Category The updated category.
     * @throws CategoryNotFoundException If the category is not found.
     * @throws \Exception On repository update failure.
     */
    public function updateCategory(int $id, array $data): Category
    {
        // --- Business Logic ---

        // 1. Check if category exists
        $category = $this->categoryRepository->findById($id);
        if (!$category) {
            throw new CategoryNotFoundException("Category with ID {$id} not found for update.");
        }

        // 2. Regenerate slug if name changes and slug is not explicitly provided
        if (isset($data['name']) && (!isset($data['slug']) || empty($data['slug']))) {
            $data['slug'] = Str::slug($data['name']);
            // Optional: Add logic to ensure new slug uniqueness before saving
        } elseif (isset($data['slug']) && empty($data['slug'])) {
            // If slug is explicitly set to empty, remove it from data so it doesn't overwrite
            unset($data['slug']);
        }


        // --- Call Repository to update data ---
        try {
            $updatedCategory = $this->categoryRepository->update($id, $data);
            if (!$updatedCategory) {
                throw new \Exception("Repository failed to update category ID {$id}.", 500);
            }
            return $updatedCategory;
        } catch (\Exception $e) {
            Log::error("CategoryService failed to update category ID {$id}: " . $e->getMessage(), ['data' => $data]);
            throw new \Exception("Failed to update category.", 500, $e);
        }
    }

    /**
     * Delete a category.
     * Business logic: Check for associated products, maybe reassign products.
     *
     * @param int $id The category ID.
     * @return bool True on successful deletion.
     * @throws CategoryNotFoundException If the category is not found.
     * @throws \Exception On repository deletion failure or business rule violation.
     */
    public function deleteCategory(int $id): bool
    {
        // --- Business Logic ---

        // 1. Check if category exists
        $category = $this->categoryRepository->findById($id);
        if (!$category) {
            throw new CategoryNotFoundException("Category with ID {$id} not found for deletion.");
        }

        // 2. Check for associated products
        // if ($category->products()->exists()) { // Requires 'products' relationship on Category model
        //      // Option A: Throw an error
        //      throw new \Exception("Cannot delete category ID {$id} because it has associated products.", 400);
        //      // Option B: Reassign products to a default category or set category_id to null
        //      // $category->products()->update(['category_id' => null]);
        // }

        // --- Call Repository to delete data ---
        try {
            $deleted = $this->categoryRepository->delete($id);
            if (!$deleted) {
                throw new \Exception("Repository failed to delete category ID {$id}.", 500);
            }
            return $deleted;
        } catch (\Exception $e) {
            Log::error("CategoryService failed to delete category ID {$id}: " . $e->getMessage());
            throw new \Exception("Failed to delete category.", 500, $e);
        }
    }
}
