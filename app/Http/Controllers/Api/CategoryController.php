<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CategoryService;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Exceptions\CategoryNotFoundException;
use App\Exceptions\InvalidInputException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * API Controller for managing Category resources.
 * Assumes only Manager and Admin can manage categories via API.
 * Authorization is handled by middleware.
 * Returns JSON responses.
 */
class CategoryController extends Controller
{
    protected CategoryService $categoryService;

    /**
     * Constructor.
     *
     * @param CategoryService $categoryService The category service instance.
     */
    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
        // Apply middleware for API authentication and authorization
        // $this->middleware('auth:sanctum'); // Require authentication
        // $this->middleware('can:manage categories'); // Require permission for all methods in this controller
    }

    /**
     * Display a listing of the categories.
     * Requires 'manage categories' permission.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Authorization is handled by the middleware in the constructor

        try {
            $categories = $this->categoryService->getAllCategories();
            return response()->json($categories);
        } catch (Exception $e) {
            Log::error("Api\CategoryController failed to load category index: " . $e->getMessage());
            return response()->json(['error' => 'Failed to load categories.'], 500);
        }
    }

    /**
     * Store a newly created category in storage.
     * Validation handled by StoreCategoryRequest.
     * Requires 'create categories' permission (checked in Form Request or middleware).
     *
     * @param StoreCategoryRequest $request The validated form request.
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreCategoryRequest $request)
    {
        // Authorization is handled by the Form Request's authorize() method or middleware

        /** @var StoreCategoryRequest|\Illuminate\Http\Request $request  */
        try {
            $category = $this->categoryService->createCategory($request->validated());
            return response()->json($category, 201); // 201 Created
        } catch (InvalidInputException $e) {
             Log::warning("Api\CategoryController - Invalid input during category creation: " . $e->getMessage());
             return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 422);
        }
        // Catch AuthorizationException if middleware is not used and Policy/Gate is checked manually
        // catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        //      return response()->json(['error' => $e->getMessage() ?: 'Unauthorized.'], 403);
        // }
        // Catch other potential exceptions
        catch (Exception $e) {
            Log::error("Api\CategoryController failed to store category: " . $e->getMessage(), ['request' => $request->all()]);
            return response()->json(['error' => 'An error occurred while creating the category.'], 500);
        }
    }

    /**
     * Display the specified category.
     * Requires 'manage categories' permission AND authorization to view THIS category (less common for categories).
     *
     * @param int $id The category ID.
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        try {
            $category = $this->categoryService->getCategoryById($id);
             // Authorization check (example using Policy - less common for categories)
            // $this->authorize('view', $category);

            return response()->json($category);
        } catch (CategoryNotFoundException $e) {
            Log::warning("Api\CategoryController - Show: Category ID {$id} not found.");
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 404);
        }
         // Catch AuthorizationException if middleware is not used and Policy/Gate is checked manually
        // catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        //      return response()->json(['error' => $e->getMessage() ?: 'Unauthorized.'], 403);
        // }
        catch (Exception $e) {
            Log::error("Api\CategoryController failed to show category ID {$id}: " . $e->getMessage());
            return response()->json(['error' => 'An error occurred while loading the category.'], 500);
        }
    }

    /**
     * Update the specified category in storage.
     * Validation handled by UpdateCategoryRequest.
     * Requires 'update categories' permission AND authorization (checked in Form Request or middleware).
     *
     * @param UpdateCategoryRequest $request The validated form request.
     * @param int $id The category ID.
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateCategoryRequest $request, int $id)
    {
        // Authorization is handled by the Form Request's authorize() method or middleware

        /** @var UpdateCategoryRequest|\Illuminate\Http\Request $request  */
        try {
            $updatedCategory = $this->categoryService->updateCategory($id, $request->validated());
            return response()->json($updatedCategory);
        } catch (CategoryNotFoundException $e) {
            Log::warning("Api\CategoryController - Update: Category ID {$id} not found.");
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 404);
        } catch (InvalidInputException $e) {
             Log::warning("Api\CategoryController - Invalid input during category update: " . $e->getMessage());
             return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 422);
        }
         // Catch AuthorizationException if middleware is not used and Policy/Gate is checked manually
        // catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        //      return response()->json(['error' => $e->getMessage() ?: 'Unauthorized.'], 403);
        // }
        catch (Exception $e) {
            Log::error("Api\CategoryController failed to update category ID {$id}: " . $e->getMessage(), ['request' => $request->all()]);
            return response()->json(['error' => 'An error occurred while updating the category.'], 500);
        }
    }

    /**
     * Remove the specified category from storage.
     * Requires 'delete categories' permission AND authorization (checked via middleware or manual check).
     *
     * @param int $id The category ID.
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id)
    {
         try {
            // Get the category first to pass to authorize (if using Policies)
            $category = $this->categoryService->getCategoryById($id); // This can throw CategoryNotFoundException

            // Authorization check (example using Policy)
            // $this->authorize('delete', $category); // Throws AuthorizationException if not allowed

            // If authorization is handled by a middleware or Gate earlier, you might not need the above authorize call here.
            // Ensure the user has the general permission first
            // Gate::authorize('delete categories'); // Throws AuthorizationException if not allowed

            $this->categoryService->deleteCategory($id);
            return response()->json(null, 204); // 204 No Content
        } catch (CategoryNotFoundException $e) {
            Log::warning("Api\CategoryController - Destroy: Category ID {$id} not found.");
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 404);
        } catch (InvalidInputException $e) {
             // Catch business logic errors like "cannot delete category with products"
             Log::warning("Api\CategoryController - Invalid input during category deletion: " . $e->getMessage());
             return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 422);
        }
         // Catch AuthorizationException if middleware is not used and Policy/Gate is checked manually
        // catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        //      return response()->json(['error' => $e->getMessage() ?: 'Unauthorized.'], 403);
        // }
        catch (Exception $e) {
            Log::error("Api\CategoryController failed to delete category ID {$id}: " . $e->getMessage());
            return response()->json(['error' => 'An error occurred while deleting the category.'], 500);
        }
    }
}
