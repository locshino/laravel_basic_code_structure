<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Exceptions\CategoryNotFoundException;
use App\Exceptions\PermissionDeniedException; // If authorization is in Controller
use App\Exceptions\InvalidInputException; // Catch business logic validation errors
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate; // For Laravel Gates
use Illuminate\Support\Facades\Auth; // For Auth checks
use Exception;

/**
 * Controller for managing Category resources.
 * Assumes only Manager and Admin can manage categories.
 * Authorization is handled by the middleware in the constructor.
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
        // Apply middleware for authorization
        // Requires user to have 'manage categories' permission
        // This checks if the authenticated user has the permission via Spatie or a Gate
        $this->middleware('can:manage categories');
    }

    /**
     * Display a listing of the categories.
     * Requires 'manage categories' permission.
     *
     * @param Request $request The incoming request.
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        // Authorization is handled by the middleware in the constructor

        try {
            $categories = $this->categoryService->getAllCategories();

            if ($request->wantsJson()) {
                return response()->json($categories);
            }

            return view('categories.index', compact('categories'));
        } catch (Exception $e) {
            Log::error("CategoryController failed to load category index: " . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to load categories.'], 500);
            }
            return redirect()->back()->with('error', 'An error occurred while loading categories.');
        }
    }

    /**
     * Show the form for creating a new category.
     * Requires 'create categories' permission.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create()
    {
        // Authorization is handled by the middleware or Form Request

        try {
            return view('categories.create');
        } catch (Exception $e) {
            Log::error("CategoryController failed to show create category form: " . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred.');
        }
    }

    /**
     * Store a newly created category in storage.
     * Validation handled by StoreCategoryRequest.
     * Requires 'create categories' permission (checked in Form Request).
     *
     * @param StoreCategoryRequest $request The validated form request.
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(StoreCategoryRequest $request)
    {
        // Authorization is handled by the Form Request's authorize() method

        /** @var StoreCategoryRequest|\Illuminate\Http\Request $request */
        try {
            $category = $this->categoryService->createCategory($request->validated());

            if ($request->wantsJson()) {
                return response()->json($category, 201);
            }

            return redirect()->route('categories.index')->with('success', 'Category created successfully!');
        } catch (InvalidInputException $e) {
            Log::warning("CategoryController - Invalid input during category creation: " . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 422);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
        // Catch other potential exceptions
        catch (Exception $e) {
            Log::error("CategoryController failed to store category: " . $e->getMessage(), ['request' => $request->all()]);
            if ($request->wantsJson()) {
                return response()->json(['error' => 'An error occurred while creating the category.'], 500);
            }
            return redirect()->back()->withInput()->with('error', 'An error occurred while creating the category. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified category.
     * Requires 'update categories' permission AND authorization to update THIS category.
     *
     * @param int $id The category ID.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(int $id)
    {
        try {
            $category = $this->categoryService->getCategoryById($id);

            // Authorization check (example using Policy - assuming CategoryPolicy exists)
            // $this->authorize('update', $category); // Throws AuthorizationException if not allowed

            return view('categories.edit', compact('category'));
        } catch (CategoryNotFoundException $e) {
            Log::warning("CategoryController - Edit: Category ID {$id} not found.");
            return redirect()->route('categories.index')->with('error', $e->getMessage());
            // Or abort(404, $e->getMessage());
        }
        // Catch AuthorizationException if using Policies/Gates directly here
        // catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        //      return redirect()->back()->with('error', $e->getMessage() ?: 'You are not authorized to perform this action.');
        // }
        catch (Exception $e) {
            Log::error("CategoryController failed to show edit form for category ID {$id}: " . $e->getMessage());
            return redirect()->route('categories.index')->with('error', 'An error occurred while loading the category for editing.');
        }
    }

    /**
     * Update the specified category in storage.
     * Validation handled by UpdateCategoryRequest.
     * Requires 'update categories' permission AND authorization (checked in Form Request).
     *
     * @param UpdateCategoryRequest $request The validated form request.
     * @param int $id The category ID.
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateCategoryRequest $request, int $id)
    {
        // Authorization is handled by the Form Request's authorize() method

        /** @var UpdateCategoryRequest|\Illuminate\Http\Request $request */
        try {
            $updatedCategory = $this->categoryService->updateCategory($id, $request->validated());

            if ($request->wantsJson()) {
                return response()->json($updatedCategory);
            }

            return redirect()->route('categories.index')->with('success', 'Category updated successfully!');
        } catch (CategoryNotFoundException $e) {
            Log::warning("CategoryController - Update: Category ID {$id} not found.");
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 404);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (InvalidInputException $e) {
            Log::warning("CategoryController - Invalid input during category update: " . $e->getMessage());
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
            Log::error("CategoryController failed to update category ID {$id}: " . $e->getMessage(), ['request' => $request->all()]);
            if ($request->wantsJson()) {
                return response()->json(['error' => 'An error occurred while updating the category.'], 500);
            }
            return redirect()->back()->withInput()->with('error', 'An error occurred while updating the category. Please try again.');
        }
    }

    /**
     * Remove the specified category from storage.
     * Requires 'delete categories' permission AND authorization to delete THIS category.
     *
     * @param Request $request The incoming request.
     * @param int $id The category ID.
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, int $id)
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

            if ($request->wantsJson()) {
                return response()->json(null, 204); // 204 No Content
            }

            return redirect()->route('categories.index')->with('success', 'Category deleted successfully!');
        } catch (CategoryNotFoundException $e) {
            Log::warning("CategoryController - Destroy: Category ID {$id} not found.");
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 404);
            }
            return redirect()->route('categories.index')->with('error', $e->getMessage());
        }
        // Catch AuthorizationException if using Policies/Gates directly here
        // catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        //      if ($request->wantsJson()) {
        //          return response()->json(['error' => $e->getMessage() ?: 'Unauthorized.'], 403);
        //      }
        //      return redirect()->back()->with('error', $e->getMessage() ?: 'You are not authorized to perform this action.');
        // }
        catch (Exception $e) {
            Log::error("CategoryController failed to delete category ID {$id}: " . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['error' => 'An error occurred while deleting the category.'], 500);
            }
            return redirect()->back()->with('error', 'An error occurred while deleting the category. Please try again.');
        }
    }
}
