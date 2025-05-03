<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; // Extend the base Controller
use App\Services\ProductService;
use App\Services\CategoryService; // Need Category Service if creating/updating products via API
use App\Http\Requests\Product\StoreProductRequest; // Reuse Form Requests for validation
use App\Http\Requests\Product\UpdateProductRequest; // Reuse Form Requests for validation
use App\Exceptions\ProductNotFoundException;
use App\Exceptions\CategoryNotFoundException;
use App\Exceptions\InvalidInputException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth; // For Auth checks
use Exception;

/**
 * API Controller for managing Product resources.
 * Returns JSON responses.
 */
class ProductController extends Controller
{
    protected ProductService $productService;
    protected CategoryService $categoryService; // Inject Category Service

    /**
     * Constructor.
     *
     * @param ProductService $productService The product service instance.
     * @param CategoryService $categoryService The category service instance.
     */
    public function __construct(ProductService $productService, CategoryService $categoryService)
    {
        $this->productService = $productService;
        $this->categoryService = $categoryService;
        // Apply middleware for API authentication (e.g., Sanctum) and authorization
        // $this->middleware('auth:sanctum')->except(['index', 'show']); // Require authentication for all except index/show
        // $this->middleware('can:create products')->only(['store']); // Require permission for store
        // $this->middleware('can:update products')->only(['update']); // Require permission for update
        // $this->middleware('can:delete products')->only(['destroy']); // Require permission for destroy
    }

    /**
     * Display a listing of the products.
     * Guest: Only active products. Authenticated (via API token): All products (based on permission).
     *
     * @param Request $request The incoming request.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Authorization check: Allow viewing all products if user can 'view all products', otherwise only active
            // Check if authenticated via Sanctum and has permission
            $includeInactive = Auth::guard('sanctum')->check() && Auth::guard('sanctum')->user()->can('view all products');

            $products = $this->productService->getAllProducts($includeInactive);

            return response()->json($products);
        } catch (Exception $e) {
            Log::error("Api\ProductController failed to load product index: " . $e->getMessage());
            return response()->json(['error' => 'Failed to load products.'], 500);
        }
    }

    /**
     * Store a newly created product in storage.
     * Validation handled by StoreProductRequest.
     * Requires 'create products' permission (checked via middleware or manual check).
     *
     * @param StoreProductRequest $request The validated form request.
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreProductRequest $request)
    {
        // Authorization is handled by the Form Request's authorize() method or middleware

        /** @var StoreProductRequest|\Illuminate\Http\Request $request */
        try {
            // Call the service layer to handle the creation logic
            $product = $this->productService->createProduct($request->validated());

            return response()->json($product, 201); // 201 Created

        } catch (CategoryNotFoundException $e) {
            Log::warning("Api\ProductController - Category not found during product creation: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400); // 400 Bad Request
        } catch (InvalidInputException $e) {
            Log::warning("Api\ProductController - Invalid input during product creation: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 422); // 422 Unprocessable Entity
        }
        // Catch AuthorizationException if middleware is not used and Policy/Gate is checked manually
        // catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        //      return response()->json(['error' => $e->getMessage() ?: 'Unauthorized.'], 403);
        // }
        // Catch other potential exceptions
        catch (Exception $e) {
            Log::error("Api\ProductController failed to store product: " . $e->getMessage(), ['request' => $request->all()]);
            return response()->json(['error' => 'An error occurred while creating the product.'], 500); // 500 Internal Server Error
        }
    }

    /**
     * Display the specified product.
     * Guest: Only active products. Authenticated: Any product (based on permission).
     *
     * @param int $id The product ID.
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        try {
            $product = $this->productService->getProductById($id);

            // Authorization check (example using Policy - assuming ProductPolicy exists)
            // If guest, Policy might check $product->is_active
            // $this->authorize('view', $product); // Throws AuthorizationException if not allowed

            return response()->json($product);
        } catch (ProductNotFoundException $e) {
            Log::warning("Api\ProductController - Show: Product ID {$id} not found.");
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 404); // 404 Not Found
        }
        // Catch AuthorizationException if middleware is not used and Policy/Gate is checked manually
        // catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        //      return response()->json(['error' => $e->getMessage() ?: 'Unauthorized.'], 403);
        // }
        catch (Exception $e) {
            Log::error("Api\ProductController failed to show product ID {$id}: " . $e->getMessage());
            return response()->json(['error' => 'An error occurred while loading the product.'], 500); // 500 Internal Server Error
        }
    }

    /**
     * Update the specified product in storage.
     * Validation handled by UpdateProductRequest.
     * Requires 'update products' permission AND authorization (checked in Form Request or middleware).
     *
     * @param UpdateProductRequest $request The validated form request.
     * @param int $id The product ID.
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateProductRequest $request, int $id)
    {
        // Authorization is handled by the Form Request's authorize() method or middleware

        /** @var UpdateProductRequest|\Illuminate\Http\Request $request */
        try {
            $updatedProduct = $this->productService->updateProduct($id, $request->validated());

            return response()->json($updatedProduct);
        } catch (ProductNotFoundException $e) {
            Log::warning("Api\ProductController - Update: Product ID {$id} not found.");
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 404); // 404 Not Found
        } catch (CategoryNotFoundException $e) {
            Log::warning("Api\ProductController - Category not found during product update: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400); // 400 Bad Request
        } catch (InvalidInputException $e) {
            Log::warning("Api\ProductController - Invalid input during product update: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 422); // 422 Unprocessable Entity
        }
        // Catch AuthorizationException if middleware is not used and Policy/Gate is checked manually
        // catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        //      return response()->json(['error' => $e->getMessage() ?: 'Unauthorized.'], 403);
        // }
        catch (Exception $e) {
            Log::error("Api\ProductController failed to update product ID {$id}: " . $e->getMessage(), ['request' => $request->all()]);
            return response()->json(['error' => 'An error occurred while updating the product.'], 500); // 500 Internal Server Error
        }
    }

    /**
     * Remove the specified product from storage.
     * Requires 'delete products' permission AND authorization (checked via middleware or manual check).
     *
     * @param int $id The product ID.
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id)
    {
        try {
            // Get the product first to pass to authorize (if using Policies)
            $product = $this->productService->getProductById($id); // This can throw ProductNotFoundException

            // Authorization check (example using Policy)
            // $this->authorize('delete', $product); // Throws AuthorizationException if not allowed

            // If authorization is handled by a middleware or Gate earlier, you might not need the above authorize call here.
            // Ensure the user has the general permission first
            // Gate::authorize('delete products'); // Throws AuthorizationException if not allowed


            $this->productService->deleteProduct($id);

            return response()->json(null, 204); // 204 No Content (Successful deletion with no content)

        } catch (ProductNotFoundException $e) {
            Log::warning("Api\ProductController - Destroy: Product ID {$id} not found.");
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 404); // 404 Not Found
        } catch (InvalidInputException $e) {
            // Catch business logic errors like "cannot delete product in active order"
            Log::warning("Api\ProductController - Invalid input during product deletion: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 422); // 422 Unprocessable Entity
        }
        // Catch AuthorizationException if middleware is not used and Policy/Gate is checked manually
        // catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        //      return response()->json(['error' => $e->getMessage() ?: 'Unauthorized.'], 403);
        // }
        catch (Exception $e) {
            Log::error("Api\ProductController failed to delete product ID {$id}: " . $e->getMessage());
            return response()->json(['error' => 'An error occurred while deleting the product.'], 500); // 500 Internal Server Error
        }
    }
}
