<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\AssignRoleRequest; // Reuse AssignRoleRequest
use App\Exceptions\UserNotFoundException;
use App\Exceptions\RoleNotFoundException;
use App\Exceptions\PermissionDeniedException;
use App\Exceptions\InvalidInputException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth; // For Auth checks
use Exception;

/**
 * API Controller for managing User resources.
 * Assumes only Admin can manage users and roles via API.
 * Authorization is handled by middleware.
 * Returns JSON responses.
 */
class UserController extends Controller
{
    protected UserService $userService;

    /**
     * Constructor.
     *
     * @param UserService $userService The user service instance.
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
        // Apply middleware for API authentication and authorization
        // $this->middleware('auth:sanctum'); // Require authentication
        // $this->middleware('can:manage users'); // Require permission for all methods in this controller
    }

    /**
     * Display a listing of the users.
     * Requires 'manage users' permission.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Authorization is handled by the middleware

        try {
            $users = $this->userService->getAllUsers();
            return response()->json($users);
        } catch (Exception $e) {
            Log::error("Api\UserController failed to load user index: " . $e->getMessage());
            return response()->json(['error' => 'Failed to load users.'], 500);
        }
    }

    /**
     * Store a newly created user in storage.
     * Validation handled by StoreUserRequest.
     * Requires 'create users' permission (checked in Form Request or middleware).
     *
     * @param StoreUserRequest $request The validated form request.
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreUserRequest $request)
    {
        // Authorization is handled by the Form Request's authorize() method or middleware

        /** @var StoreUserRequest|\Illuminate\Http\Request $request  */
        try {
            $user = $this->userService->createUser($request->validated());

            // Handle role assignment if included in the request data
            if ($request->has('role_id')) {
                 // Ensure role_id is validated in the Form Request or manually here
                 $this->userService->assignRoleToUser($user->id, $request->input('role_id')); // Example: Assigning a single role
            }

            return response()->json($user, 201); // 201 Created

        } catch (InvalidInputException $e) {
             Log::warning("Api\UserController - Invalid input during user creation: " . $e->getMessage());
             return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 422);
        } catch (RoleNotFoundException $e) {
             Log::warning("Api\UserController - Role not found during user creation: " . $e->getMessage());
             return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400); // Bad Request for invalid role
        }
        // Catch AuthorizationException if middleware is not used and Policy/Gate is checked manually
        // catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        //      return response()->json(['error' => $e->getMessage() ?: 'Unauthorized.'], 403);
        // }
        // Catch other potential exceptions
        catch (Exception $e) {
            Log::error("Api\UserController failed to store user: " . $e->getMessage(), ['request' => $request->all()]);
            return response()->json(['error' => 'An error occurred while creating the user.'], 500);
        }
    }

    /**
     * Display the specified user.
     * Requires 'manage users' permission AND authorization to view THIS user.
     *
     * @param int $id The user ID.
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        try {
            $user = $this->userService->getUserById($id);

            // Authorization check (example using Policy - assuming UserPolicy exists)
            // $this->authorize('view', $user); // Throws AuthorizationException if not allowed

            return response()->json($user);

        } catch (UserNotFoundException $e) {
            Log::warning("Api\UserController - Show: User ID {$id} not found.");
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 404);
        }
         // Catch AuthorizationException if middleware is not used and Policy/Gate is checked manually
        // catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        //      return response()->json(['error' => $e->getMessage() ?: 'Unauthorized.'], 403);
        // }
        catch (Exception $e) {
            Log::error("Api\UserController failed to show user ID {$id}: " . $e->getMessage());
            return response()->json(['error' => 'An error occurred while loading the user.'], 500);
        }
    }

    /**
     * Update the specified user in storage.
     * Validation handled by UpdateUserRequest.
     * Requires 'update users' permission AND authorization (checked in Form Request or middleware).
     *
     * @param UpdateUserRequest $request The validated form request.
     * @param int $id The user ID.
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateUserRequest $request, int $id)
    {
        // Authorization is handled by the Form Request's authorize() method or middleware

        /** @var UpdateUserRequest|\Illuminate\Http\Request $request  */
        try {
            $updatedUser = $this->userService->updateUser($id, $request->validated());

            // Handle role assignment/removal if included in the request data
            // if ($request->has('role_id')) {
            //     // Logic to sync/assign/remove roles
            //     // You might need a dedicated method in UserService like syncUserRoles or updateRoles
            //     $this->userService->assignRoleToUser($updatedUser->id, $request->role_id); // Example: Assigning a single role
            // }

            return response()->json($updatedUser);

        } catch (UserNotFoundException $e) {
            Log::warning("Api\UserController - Update: User ID {$id} not found.");
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 404);
        } catch (InvalidInputException $e) {
             Log::warning("Api\UserController - Invalid input during user update: " . $e->getMessage());
             return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 422);
        }
        // Catch AuthorizationException if middleware is not used and Policy/Gate is checked manually
        // catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        //      return response()->json(['error' => $e->getMessage() ?: 'Unauthorized.'], 403);
        // }
        catch (Exception $e) {
            Log::error("Api\UserController failed to update user ID {$id}: " . $e->getMessage(), ['request' => $request->all()]);
            return response()->json(['error' => 'An error occurred while updating the user.'], 500);
        }
    }

    /**
     * Remove the specified user from storage.
     * Requires 'delete users' permission AND authorization (checked via middleware or manual check).
     *
     * @param int $id The user ID.
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id)
    {
         try {
            // Get the user first to pass to authorize (if using Policies)
            $user = $this->userService->getUserById($id); // This can throw UserNotFoundException

            // Authorization check (example using Policy)
            // $this->authorize('delete', $user); // Throws AuthorizationException if not allowed

            // If authorization is handled by a middleware or Gate earlier, you might not need the above authorize call here.
            // Ensure the user has the general permission first
            // Gate::authorize('delete users'); // Throws AuthorizationException if not allowed

            $this->userService->deleteUser($id);

            return response()->json(null, 204); // 204 No Content
        } catch (UserNotFoundException $e) {
            Log::warning("Api\UserController - Destroy: User ID {$id} not found.");
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 404);
        } catch (InvalidInputException $e) {
             Log::warning("Api\UserController - Invalid input during user deletion: " . $e->getMessage());
             return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 422);
        }
        // Catch AuthorizationException if middleware is not used and Policy/Gate is checked manually
        // catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        //      return response()->json(['error' => $e->getMessage() ?: 'Unauthorized.'], 403);
        // }
        catch (Exception $e) {
            Log::error("Api\UserController failed to delete user ID {$id}: " . $e->getMessage());
            return response()->json(['error' => 'An error occurred while deleting the user.'], 500);
        }
    }

    /**
     * Assign a role to a user via API.
     * Validation handled by AssignRoleRequest.
     * Requires 'assign roles' permission AND authorization (checked in Form Request or middleware).
     *
     * @param AssignRoleRequest $request The validated form request.
     * @param int $id The user ID.
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignRole(AssignRoleRequest $request, int $id)
    {
        // Authorization is handled by the Form Request's authorize() method or middleware

        /** @var AssignRoleRequest|\Illuminate\Http\Request $request  */
        try {
            $user = $this->userService->assignRoleToUser($id, $request->input('role_id'));

            return response()->json($user);

        } catch (UserNotFoundException $e) {
            Log::warning("Api\UserController - Assign Role: User ID {$id} not found.");
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 404);
        } catch (RoleNotFoundException $e) {
             Log::warning("Api\UserController - Assign Role: Role not found: " . $e->getMessage());
             return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400); // Bad Request for invalid role
        } catch (InvalidInputException $e) {
             Log::warning("Api\UserController - Assign Role: Invalid input: " . $e->getMessage());
             return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 422);
        }
        // Catch AuthorizationException if middleware is not used and Policy/Gate is checked manually
        // catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        //      return response()->json(['error' => $e->getMessage() ?: 'Unauthorized.'], 403);
        // }
        catch (Exception $e) {
            Log::error("Api\UserController failed to assign role to user ID {$id}: " . $e->getMessage(), ['request' => $request->all()]);
            return response()->json(['error' => 'An error occurred while assigning the role.'], 500);
        }
    }

    // Add methods for removing roles, managing permissions directly if needed via API
}
