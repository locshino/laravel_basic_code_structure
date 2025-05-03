<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Eloquent implementation of ProductRepositoryInterface.
 * Handles data access for Products using Eloquent ORM.
 */
class EloquentProductRepository implements ProductRepositoryInterface
{
    protected Product $productModel;

    /**
     * Constructor.
     *
     * @param Product $productModel The Product model instance.
     */
    public function __construct(Product $productModel)
    {
        $this->productModel = $productModel;
    }

    /**
     * Get all products.
     *
     * @return Collection<int, Product>
     */
    public function getAll(): Collection
    {
        return $this->productModel->all();
    }

    /**
     * Get all active products.
     *
     * @return Collection<int, Product>
     */
    public function getAllActive(): Collection
    {
        return $this->productModel->where('is_active', true)->get();
    }

    /**
     * Find a product by its ID.
     *
     * @param int $id The product ID.
     * @return Product|null The product model or null if not found.
     */
    public function findById(int $id): ?Product
    {
        return $this->productModel->where('id', $id)->first();
    }

    /**
     * Find a product by its slug.
     *
     * @param string $slug The product slug.
     * @return Product|null The product model or null if not found.
     */
    public function findBySlug(string $slug): ?Product
    {
        return $this->productModel->where('slug', $slug)->first();
    }

    /**
     * Create a new product.
     *
     * @param array $data The product data.
     * @return Product The created product model.
     */
    public function create(array $data): Product
    {
        // Eloquent's create method handles mass assignment based on $fillable
        return $this->productModel->create($data);
    }

    /**
     * Update a product by its ID.
     *
     * @param int $id The product ID.
     * @param array $data The data to update.
     * @return Product|null The updated product model or null if not found.
     */
    public function update(int $id, array $data): ?Product
    {
        $product = $this->findById($id);
        if (!$product) {
            return null; // Service layer will handle ProductNotFoundException
        }
        $product->update($data);
        return $product;
    }

    /**
     * Delete a product by its ID.
     *
     * @param int $id The product ID.
     * @return bool True on success, false on failure or not found.
     */
    public function delete(int $id): bool
    {
        $product = $this->findById($id);
        if (!$product) {
            return false; // Service layer will handle ProductNotFoundException
        }
        return $product->delete();
    }
}
