<?php

namespace App\Services;

use App\Interfaces\Repositories\ProductRepositoryInterface;
use App\Interfaces\Repositories\CategoryRepositoryInterface; // Need Category Repository
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use App\Exceptions\ProductNotFoundException;
use App\Exceptions\CategoryNotFoundException;
use App\Exceptions\InvalidInputException; // Example for business logic validation
use Illuminate\Support\Str; // For slug generation
use Illuminate\Support\Facades\Log; // For logging errors
use Illuminate\Support\Facades\Storage;

/**
 * Service layer for Product related business logic.
 * Handles business rules and orchestrates data access via Repository.
 */
class ProductService
{
    protected ProductRepositoryInterface $productRepository;
    protected CategoryRepositoryInterface $categoryRepository; // Inject Category Repository

    /**
     * Constructor.
     *
     * @param ProductRepositoryInterface $productRepository The product repository interface.
     * @param CategoryRepositoryInterface $categoryRepository The category repository interface.
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Get all products (active for guest, all for admin/manager).
     * Business logic: Filter active products based on user role (handled in Controller/Policy).
     *
     * @param bool $includeInactive Whether to include inactive products.
     * @return Collection<int, Product>
     */
    public function getAllProducts(bool $includeInactive = false): Collection
    {
        if ($includeInactive) {
            return $this->productRepository->getAll();
        }
        return $this->productRepository->getAllActive();
    }

    /**
     * Get a specific product by ID.
     *
     * @param int $id The product ID.
     * @return Product
     * @throws ProductNotFoundException If the product is not found.
     */
    public function getProductById(int $id): Product
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            throw new ProductNotFoundException("Product with ID {$id} not found.");
        }

        // Add business logic here, e.g., check if active for guest
        // if (!$product->is_active && !auth()->user()->can('view-inactive-products')) {
        //     throw new ProductNotFoundException("Product with ID {$id} not found or not active.");
        // }

        return $product;
    }

    /**
     * Create a new product.
     * Business logic: Generate slug, validate category, maybe set default stock/status.
     *
     * @param array $data Product data (already validated by Form Request).
     * @return Product The created product.
     * @throws CategoryNotFoundException If the provided category ID does not exist.
     * @throws InvalidInputException If business rules are violated (e.g., negative price after calculation).
     * @throws \Exception On repository creation failure.
     */
    public function createProduct(array $data): Product
    {
        // --- Business Logic ---

        // 1. Validate category ID exists if provided
        if (isset($data['category_id']) && $data['category_id'] !== null) {
            $category = $this->categoryRepository->findById($data['category_id']);
            if (!$category) {
                throw new CategoryNotFoundException("Category with ID {$data['category_id']} not found.");
            }
        } else {
            // Ensure category_id is null if not provided
            $data['category_id'] = null;
        }


        // 2. Generate slug if not provided
        if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
            // Optional: Add logic to ensure slug uniqueness before saving
        }

        // 3. Apply business rules (e.g., default stock, active status)
        if (!isset($data['stock'])) {
            $data['stock'] = 0; // Default stock
        }
        if (!isset($data['is_active'])) {
            $data['is_active'] = true; // Default active status
        }

        // 4. Handle image upload if present in data
        if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            // Store the image and get the path
            $data['image_path'] = $data['image']->store('products', 'public'); // Store in storage/app/public/products
            unset($data['image']); // Remove file object from data array
        } else {
            unset($data['image']); // Remove if not a file upload
        }


        // --- Call Repository to persist data ---
        try {
            return $this->productRepository->create($data);
        } catch (\Exception $e) {
            // Log error at service level
            Log::error("ProductService failed to create product: " . $e->getMessage(), ['data' => $data]);
            // Re-throw a generic exception or a service-specific one
            throw new \Exception("Failed to create product due to an internal issue.", 500, $e);
        }
    }

    /**
     * Update a product.
     * Business logic: Validate category, generate slug if name changes, apply rules, handle image.
     *
     * @param int $id The product ID.
     * @param array $data Data to update (already validated by Form Request).
     * @return Product The updated product.
     * @throws ProductNotFoundException If the product is not found.
     * @throws CategoryNotFoundException If the provided category ID does not exist.
     * @throws InvalidInputException If business rules are violated.
     * @throws \Exception On repository update failure.
     */
    public function updateProduct(int $id, array $data): Product
    {
        // --- Business Logic ---

        // 1. Check if product exists (Repository might return null or throw, Service handles it)
        $product = $this->productRepository->findById($id);
        if (!$product) {
            throw new ProductNotFoundException("Product with ID {$id} not found for update.");
        }

        // 2. Validate category ID if provided
        if (isset($data['category_id']) && $data['category_id'] !== null) {
            $category = $this->categoryRepository->findById($data['category_id']);
            if (!$category) {
                throw new CategoryNotFoundException("Category with ID {$data['category_id']} not found for product update.");
            }
        } else {
            // Ensure category_id is null if explicitly set to null or empty in data
            if (array_key_exists('category_id', $data) && ($data['category_id'] === null || $data['category_id'] === '')) {
                $data['category_id'] = null;
            }
        }

        // 3. Regenerate slug if name changes and slug is not explicitly provided
        if (isset($data['name']) && (!isset($data['slug']) || empty($data['slug']))) {
            $data['slug'] = Str::slug($data['name']);
            // Optional: Add logic to ensure new slug uniqueness before saving
        } elseif (isset($data['slug']) && empty($data['slug'])) {
            // If slug is explicitly set to empty, remove it from data so it doesn't overwrite
            unset($data['slug']);
        }


        // 4. Handle image upload if present in data
        if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            // Delete old image if exists
            if ($product->image_path) {
                \Storage::disk('public')->delete($product->image_path);
            }
            // Store the new image
            $data['image_path'] = $data['image']->store('products', 'public');
            unset($data['image']); // Remove file object
        } elseif (array_key_exists('image', $data) && $data['image'] === null) {
            // If image is explicitly set to null in data (e.g., checkbox "remove image")
            if ($product->image_path) {
                \Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = null;
            unset($data['image']);
        } else {
            unset($data['image']); // Remove if not a file upload and not explicitly set to null
        }


        // --- Call Repository to update data ---
        try {
            $updatedProduct = $this->productRepository->update($id, $data);
            if (!$updatedProduct) {
                // This case is handled by findById check above, but good practice to check
                throw new \Exception("Repository failed to update product ID {$id}.", 500);
            }
            return $updatedProduct;
        } catch (\Exception $e) {
            // Log error at service level
            Log::error("ProductService failed to update product ID {$id}: " . $e->getMessage(), ['data' => $data]);
            // Re-throw a generic exception or a service-specific one
            throw new \Exception("Failed to update product.", 500, $e);
        }
    }

    /**
     * Delete a product.
     * Business logic: Check dependencies, archive instead of delete? Delete associated image.
     *
     * @param int $id The product ID.
     * @return bool True on successful deletion.
     * @throws ProductNotFoundException If the product is not found.
     * @throws \Exception On repository deletion failure or business rule violation.
     */
    public function deleteProduct(int $id): bool
    {
        // --- Business Logic ---

        // 1. Check if product exists
        $product = $this->productRepository->findById($id);
        if (!$product) {
            throw new ProductNotFoundException("Product with ID {$id} not found for deletion.");
        }

        // 2. Check for dependencies (e.g., if product is in active orders)
        // if ($product->orders()->where('status', '!=', 'completed')->exists()) { // Requires 'orders' relationship
        //      throw new \Exception("Cannot delete product ID {$id} because it is part of active orders.", 400);
        // }

        // --- Call Repository to delete data ---
        try {
            // Delete associated image before deleting the product record
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            $deleted = $this->productRepository->delete($id);
            if (!$deleted) {
                throw new \Exception("Repository failed to delete product ID {$id}.", 500);
            }
            return $deleted;
        } catch (\Exception $e) {
            // Log error at service level
            Log::error("ProductService failed to delete product ID {$id}: " . $e->getMessage());
            // Re-throw a generic exception or a service-specific one
            throw new \Exception("Failed to delete product.", 500, $e);
        }
    }

    // Add other service methods like getProductsByCategory, searchProducts, etc.
}
