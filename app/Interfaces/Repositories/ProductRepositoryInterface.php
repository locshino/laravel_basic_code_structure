<?php

namespace App\Interfaces\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface for Product Repository.
 * Defines the contract for data access operations for Products.
 */
interface ProductRepositoryInterface
{
    /**
     * Get all products.
     *
     * @return Collection<int, Product>
     */
    public function getAll(): Collection;

    /**
     * Get all active products.
     *
     * @return Collection<int, Product>
     */
    public function getAllActive(): Collection;

    /**
     * Find a product by its ID.
     *
     * @param int $id The product ID.
     * @return Product|null The product model or null if not found.
     */
    public function findById(int $id): ?Product;

    /**
     * Find a product by its slug.
     *
     * @param string $slug The product slug.
     * @return Product|null The product model or null if not found.
     */
    public function findBySlug(string $slug): ?Product;


    /**
     * Create a new product.
     *
     * @param array $data The product data.
     * @return Product The created product model.
     */
    public function create(array $data): Product;

    /**
     * Update a product by its ID.
     *
     * @param int $id The product ID.
     * @param array $data The data to update.
     * @return Product|null The updated product model or null if not found.
     */
    public function update(int $id, array $data): ?Product;

    /**
     * Delete a product by its ID.
     *
     * @param int $id The product ID.
     * @return bool True on success, false on failure or not found.
     */
    public function delete(int $id): bool;

    // Add other data access methods as needed
}
