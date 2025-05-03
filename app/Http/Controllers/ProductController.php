<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use App\Services\CategoryService; // Need Category Service for dropdowns etc.
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Exceptions\ProductNotFoundException;
use App\Exceptions\CategoryNotFoundException;
use App\Exceptions\PermissionDeniedException; // If authorization is in Controller
use App\Exceptions\InvalidInputException; // Catch business logic validation errors
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate; // For Laravel Gates
use Illuminate\Support\Facades\Auth; // For Auth checks
use Exception;

/**
 * Controller for managing Product resources.
 * Handles incoming requests, calls the Service layer, and returns responses (Views or JSON).
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
        // Apply middleware for authorization if not using Policies/Gates in methods
        // $this->middleware('can:manage products')->except(['index', 'show']);
        // $this->middleware('can:create products')->only(['create', 'store']);
        // $this->middleware('can:update products')->only(['edit', 'update']);
        // $this->middleware('can:delete products')->only(['destroy']);
    }

    /**
     * Display a listing of the products.
     * Guest: Only active products. Manager/Admin: All products.
     *
     * @param Request $request The incoming request.
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        try {
            // Authorization check (example using Gate)
            // Allow viewing all products if user can 'view all products', otherwise only active
            $includeInactive = Auth::check() && Auth::user()->can('view all products');

            $products = $this->productService->getAllProducts($includeInactive);

            // Return view for web requests
            if ($request->wantsJson()) {
                return response()->json($products); // Return JSON for API requests
            }

            return view('products.index', compact('products'));
        } catch (Exception $e) {
            Log::error("ProductController failed to load product index: " . $e->getMessage());
            // Handle error response (e.g., redirect with error, return error JSON)
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to load products.'], 500);
            }
            return redirect()->back()->with('error', 'An error occurred while loading products.');
        }
    }

    /**
     * Show the form for creating a new product.
     * Requires 'create products' permission.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create()
    {
        // Authorization check (example using Gate)
        // Gate::authorize('create products'); // Throws AuthorizationException if not allowed

        try {
            $categories = $this->categoryService->getAllCategories();
            return view('products.create', compact('categories'));
        } catch (Exception $e) {
            Log::error("ProductController failed to show create product form: " . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred.');
        }
    }

    /**
     * Store a newly created product in storage.
     * Validation handled by StoreProductRequest.
     * Requires 'create products' permission (checked in Form Request).
     *
     * @param StoreProductRequest $request The validated form request.
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(StoreProductRequest $request)
    {
        // Authorization is handled by the Form Request's authorize() method

        /** @var StoreProductRequest|\Illuminate\Http\Request $request */
        try {
            // Call the service layer to handle the creation logic
            $product = $this->productService->createProduct($request->validated());

            // Redirect or return JSON response based on request type
            if ($request->wantsJson()) {
                return response()->json($product, 201); // 201 Created
            }

            return redirect()->route('products.show', $product->id)->with('success', 'Product created successfully!');
        } catch (CategoryNotFoundException $e) {
            // Catch specific business logic errors from Service
            Log::warning("ProductController - Category not found during product creation: " . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (InvalidInputException $e) {
            // Catch specific business logic validation errors from Service
            Log::warning("ProductController - Invalid input during product creation: " . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 422);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
        // Catch other potential exceptions (repository errors, unexpected issues)
        catch (Exception $e) {
            Log::error("ProductController failed to store product: " . $e->getMessage(), ['request' => $request->all()]);

            if ($request->wantsJson()) {
                return response()->json(['error' => 'An error occurred while creating the product.'], 500);
            }
            return redirect()->back()->withInput()->with('error', 'An error occurred while creating the product. Please try again.');
        }
    }

    /**
     * Display the specified product.
     * Guest: Only active products. Manager/Admin: Any product.
     *
     * @param Request $request The incoming request.
     * @param int $id The product ID.
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function show(Request $request, int $id)
    {
        try {
            $product = $this->productService->getProductById($id);

            // Authorization check (example using Policy - assuming ProductPolicy exists)
            // If guest, Policy might check $product->is_active
            // $this->authorize('view', $product); // Throws AuthorizationException if not allowed

            if ($request->wantsJson()) {
                return response()->json($product);
            }

            return view('products.show', compact('product'));
        } catch (ProductNotFoundException $e) {
            Log::warning("ProductController - Show: Product ID {$id} not found.");
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 404);
            }
            // Redirect to index with error, or show a 404 page
            return redirect()->route('products.index')->with('error', $e->getMessage());
            // Or abort(404, $e->getMessage());
        } catch (Exception $e) {
            Log::error("ProductController failed to show product ID {$id}: " . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['error' => 'An error occurred while loading the product.'], 500);
            }
            return redirect()->route('products.index')->with('error', 'An error occurred while loading the product.');
        }
    }

    /**
     * Show the form for editing the specified product.
     * Requires 'update products' permission AND authorization to update THIS product.
     *
     * @param int $id The product ID.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(int $id)
    {
        try {
            $product = $this->productService->getProductById($id);

            // Authorization check (example using Policy)
            // $this->authorize('update', $product); // Throws AuthorizationException if not allowed

            $categories = $this->categoryService->getAllCategories();

            return view('products.edit', compact('product', 'categories'));
        } catch (ProductNotFoundException $e) {
            Log::warning("ProductController - Edit: Product ID {$id} not found.");
            return redirect()->route('products.index')->with('error', $e->getMessage());
            // Or abort(404, $e->getMessage());
        }
        // Catch AuthorizationException if using Policies/Gates directly here
        // catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        //      return redirect()->back()->with('error', $e->getMessage() ?: 'You are not authorized to perform this action.');
        // }
        catch (Exception $e) {
            Log::error("ProductController failed to show edit form for product ID {$id}: " . $e->getMessage());
            return redirect()->route('products.index')->with('error', 'An error occurred while loading the product for editing.');
        }
    }

    /**
     * Update the specified product in storage.
     * Validation handled by UpdateProductRequest.
     * Requires 'update products' permission AND authorization (checked in Form Request).
     *
     * @param UpdateProductRequest $request The validated form request.
     * @param int $id The product ID.
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateProductRequest $request, int $id)
    {
        // Authorization is handled by the Form Request's authorize() method

        /** @var UpdateProductRequest|\Illuminate\Http\Request $request */
        try {
            // Call the service layer to perform the update logic
            $updatedProduct = $this->productService->updateProduct($id, $request->validated());

            if ($request->wantsJson()) {
                return response()->json($updatedProduct);
            }

            return redirect()->route('products.show', $updatedProduct->id)->with('success', 'Product updated successfully!');
        } catch (ProductNotFoundException $e) {
            Log::warning("ProductController - Update: Product ID {$id} not found.");
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 404);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (CategoryNotFoundException $e) {
            Log::warning("ProductController - Category not found during product update: " . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (InvalidInputException $e) {
            Log::warning("ProductController - Invalid input during product update: " . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 422);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
        // Catch AuthorizationException if using Policies/Gates directly here
        // catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        //      if ($request->wantsJson()) {
        //          return response()->json(['error' => $e->getMessage() ?: 'Unauthorized.'], 403);
        //      }
        //      return redirect()->back()->with('error', $e->getMessage() ?: 'You are not authorized to perform this action.');
        // }
        catch (Exception $e) {
            Log::error("ProductController failed to update product ID {$id}: " . $e->getMessage(), ['request' => $request->all()]);
            if ($request->wantsJson()) {
                return response()->json(['error' => 'An error occurred while updating the product.'], 500);
            }
            return redirect()->back()->withInput()->with('error', 'An error occurred while updating the product. Please try again.');
        }
    }

    /**
     * Remove the specified product from storage.
     * Requires 'delete products' permission AND authorization to delete THIS product.
     *
     * @param Request $request The incoming request.
     * @param int $id The product ID.
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, int $id)
    {
        try {
            // Get the product first to pass to authorize (if using Policies)
            $product = $this->productService->getProductById($id); // This can throw ProductNotFoundException

            // Authorization check (example using Policy)
            // $this->authorize('delete', $product); // Throws AuthorizationException if not allowed

            // If authorization is handled by a middleware or Gate earlier, you might not need the above authorize call here.
            // Ensure the user has the general permission first
            // Gate::authorize('delete products'); // Throws AuthorizationException if not allowed


            // Call the service layer to perform the deletion logic
            $this->productService->deleteProduct($id);

            if ($request->wantsJson()) {
                return response()->json(null, 204); // 204 No Content
            }

            return redirect()->route('products.index')->with('success', 'Product deleted successfully!');
        } catch (ProductNotFoundException $e) {
            Log::warning("ProductController - Destroy: Product ID {$id} not found.");
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 404);
            }
            return redirect()->route('products.index')->with('error', $e->getMessage());
        }
        // Catch AuthorizationException if using Policies/Gates directly here
        // catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        //      if ($request->wantsJson()) {
        //          return response()->json(['error' => $e->getMessage() ?: 'Unauthorized.'], 403);
        //      }
        //      return redirect()->back()->with('error', $e->getMessage() ?: 'You are not authorized to perform this action.');
        // }
        catch (Exception $e) {
            Log::error("ProductController failed to delete product ID {$id}: " . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['error' => 'An error occurred while deleting the product.'], 500);
            }
            return redirect()->back()->with('error', 'An error occurred while deleting the product. Please try again.');
        }
    }
}
