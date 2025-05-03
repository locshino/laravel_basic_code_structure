<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\AssignRoleRequest;
use App\Exceptions\UserNotFoundException;
use App\Exceptions\RoleNotFoundException;
use App\Exceptions\PermissionDeniedException;
use App\Exceptions\InvalidInputException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate; // For Laravel Gates
use Illuminate\Support\Facades\Auth; // For Auth checks
use Exception;

/**
 * Controller for managing User resources.
 * Assumes only Admin can manage users and roles.
 * Authorization is handled by the middleware in the constructor.
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
        // Apply middleware for authorization
        // Requires user to have 'manage users' permission (e.g., Admin)
        $this->middleware('can:manage users');
    }

    /**
     * Display a listing of the users.
     * Requires 'manage users' permission.
     *
     * @param Request $request The incoming request.
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        // Authorization is handled by the middleware

        try {
            $users = $this->userService->getAllUsers();

            if ($request->wantsJson()) {
                return response()->json($users);
            }

            return view('users.index', compact('users'));
        } catch (Exception $e) {
            Log::error("UserController failed to load user index: " . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to load users.'], 500);
            }
            return redirect()->back()->with('error', 'An error occurred while loading users.');
        }
    }

    /**
     * Show the form for creating a new user.
     * Requires 'create users' permission.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create()
    {
        // Authorization is handled by the middleware or Form Request

        try {
            // You might need roles here to assign on creation
            $roles = $this->userService->getAllRoles(); // Requires method in UserService
            return view('users.create', compact('roles'));
            // return view('users.create'); // If not assigning roles on creation form

        } catch (Exception $e) {
            Log::error("UserController failed to show create user form: " . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred.');
        }
    }

    /**
     * Store a newly created user in storage.
     * Validation handled by StoreUserRequest.
     * Requires 'create users' permission (checked in Form Request).
     *
     * @param StoreUserRequest $request The validated form request.
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(StoreUserRequest $request)
    {
        // Authorization is handled by the Form Request's authorize() method

        /** @var StoreUserRequest|\Illuminate\Http\Request $request */
        try {
            $user = $this->userService->createUser($request->validated());

            // Handle role assignment if included in the form request data
            if ($request->has('role_id')) {
                // Ensure the role_id is validated in the Form Request if used here
                $this->userService->assignRoleToUser($user->id, $request->input('role_id'));
            }


            if ($request->wantsJson()) {
                return response()->json($user, 201);
            }

            return redirect()->route('users.index')->with('success', 'User created successfully!');
        } catch (InvalidInputException $e) {
            Log::warning("UserController - Invalid input during user creation: " . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 422);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (RoleNotFoundException $e) {
            Log::warning("UserController - Role not found during user creation: " . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400); // Bad Request for invalid role
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
        // Catch other potential exceptions
        catch (Exception $e) {
            Log::error("UserController failed to store user: " . $e->getMessage(), ['request' => $request->all()]);
            if ($request->wantsJson()) {
                return response()->json(['error' => 'An error occurred while creating the user.'], 500);
            }
            return redirect()->back()->withInput()->with('error', 'An error occurred while creating the user. Please try again.');
        }
    }

    /**
     * Display the specified user.
     * Requires 'manage users' permission AND authorization to view THIS user.
     *
     * @param Request $request The incoming request.
     * @param int $id The user ID.
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function show(Request $request, int $id)
    {
        try {
            $user = $this->userService->getUserById($id);

            // Authorization check (example using Policy - assuming UserPolicy exists)
            // $this->authorize('view', $user); // Throws AuthorizationException if not allowed

            if ($request->wantsJson()) {
                return response()->json($user);
            }

            return view('users.show', compact('user'));
        } catch (UserNotFoundException $e) {
            Log::warning("UserController - Show: User ID {$id} not found.");
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 404);
            }
            return redirect()->route('users.index')->with('error', $e->getMessage());
            // Or abort(404, $e->getMessage());
        }
        // Catch AuthorizationException if using Policies/Gates directly here
        // catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        //      if ($request->wantsJson()) {
        //          return response()->json(['error' => $e->getMessage() ?: 'Unauthorized.'], 403);
        //      }
        //      return redirect()->back()->with('error', $e->getMessage() ?: 'You are not authorized to perform this action.');
        // }
        catch (Exception $e) {
            Log::error("UserController failed to show user ID {$id}: " . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['error' => 'An error occurred while loading the user.'], 500);
            }
            return redirect()->route('users.index')->with('error', 'An error occurred while loading the user.');
        }
    }

    /**
     * Show the form for editing the specified user.
     * Requires 'update users' permission AND authorization to update THIS user.
     *
     * @param int $id The user ID.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(int $id)
    {
        try {
            $user = $this->userService->getUserById($id);

            // Authorization check (example using Policy)
            // $this->authorize('update', $user); // Throws AuthorizationException if not allowed

            // You might need roles here to manage user roles
            $roles = $this->userService->getAllRoles(); // Requires method in UserService
            return view('users.edit', compact('user', 'roles'));
            // return view('users.edit', compact('user')); // If not managing roles on edit form

        } catch (UserNotFoundException $e) {
            Log::warning("UserController - Edit: User ID {$id} not found.");
            return redirect()->route('users.index')->with('error', $e->getMessage());
            // Or abort(404, $e->getMessage());
        }
        // Catch AuthorizationException if using Policies/Gates directly here
        // catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        //      return redirect()->back()->with('error', $e->getMessage() ?: 'You are not authorized to perform this action.');
        // }
        catch (Exception $e) {
            Log::error("UserController failed to show edit form for user ID {$id}: " . $e->getMessage());
            return redirect()->route('users.index')->with('error', 'An error occurred while loading the user for editing.');
        }
    }

    /**
     * Update the specified user in storage.
     * Validation handled by UpdateUserRequest.
     * Requires 'update users' permission AND authorization (checked in Form Request).
     *
     * @param UpdateUserRequest $request The validated form request.
     * @param int $id The user ID.
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateUserRequest $request, int $id)
    {
        // Authorization is handled by the Form Request's authorize() method

        /** @var UpdateUserRequest|\Illuminate\Http\Request $request */
        try {
            $updatedUser = $this->userService->updateUser($id, $request->validated());

            // Handle role assignment/removal if included in the form request data
            // if ($request->has('role_id')) {
            //     // Logic to sync/assign/remove roles
            //     // You might need a dedicated method in UserService like syncUserRoles or updateRoles
            //     $this->userService->assignRoleToUser($updatedUser->id, $request->role_id); // Example: Assigning a single role
            // }

            if ($request->wantsJson()) {
                return response()->json($updatedUser);
            }

            return redirect()->route('users.index')->with('success', 'User updated successfully!');
        } catch (UserNotFoundException $e) {
            Log::warning("UserController - Update: User ID {$id} not found.");
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 404);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (InvalidInputException $e) {
            Log::warning("UserController - Invalid input during user update: " . $e->getMessage());
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
            Log::error("UserController failed to update user ID {$id}: " . $e->getMessage(), ['request' => $request->all()]);
            if ($request->wantsJson()) {
                return response()->json(['error' => 'An error occurred while updating the user.'], 500);
            }
            return redirect()->back()->withInput()->with('error', 'An error occurred while updating the user. Please try again.');
        }
    }

    /**
     * Remove the specified user from storage.
     * Requires 'delete users' permission AND authorization to delete THIS user.
     *
     * @param Request $request The incoming request.
     * @param int $id The user ID.
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, int $id)
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

            if ($request->wantsJson()) {
                return response()->json(null, 204); // 204 No Content
            }

            return redirect()->route('users.index')->with('success', 'User deleted successfully!');
        } catch (UserNotFoundException $e) {
            Log::warning("UserController - Destroy: User ID {$id} not found.");
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 404);
            }
            return redirect()->route('users.index')->with('error', $e->getMessage());
        } catch (InvalidInputException $e) {
            Log::warning("UserController - Invalid input during user deletion: " . $e->getMessage());
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
            Log::error("UserController failed to delete user ID {$id}: " . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['error' => 'An error occurred while deleting the user.'], 500);
            }
            return redirect()->back()->with('error', 'An error occurred while deleting the user. Please try again.');
        }
    }

    /**
     * Show the form for assigning roles to a user.
     * Requires 'assign roles' permission AND authorization to assign role to THIS user.
     *
     * @param int $id The user ID.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showAssignRoleForm(int $id)
    {
        try {
            $user = $this->userService->getUserById($id);

            // Authorization check (example using Policy)
            // $this->authorize('assignRole', $user); // Requires method in UserPolicy

            // Get all available roles
            $roles = $this->userService->getAllRoles(); // Requires method in UserService

            return view('users.assign_role', compact('user', 'roles'));
        } catch (UserNotFoundException $e) {
            Log::warning("UserController - Show Assign Role: User ID {$id} not found.");
            return redirect()->route('users.index')->with('error', $e->getMessage());
        }
        // Catch AuthorizationException if using Policies/Gates directly here
        // catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        //      return redirect()->back()->with('error', $e->getMessage() ?: 'You are not authorized to perform this action.');
        // }
        catch (Exception $e) {
            Log::error("UserController failed to show assign role form for user ID {$id}: " . $e->getMessage());
            return redirect()->route('users.index')->with('error', 'An error occurred.');
        }
    }

    /**
     * Assign a role to a user.
     * Validation handled by AssignRoleRequest.
     * Requires 'assign roles' permission AND authorization (checked in Form Request).
     *
     * @param AssignRoleRequest $request The validated form request.
     * @param int $id The user ID.
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function assignRole(AssignRoleRequest $request, int $id)
    {
        // Authorization is handled by the Form Request's authorize() method

        /** @var AssignRoleRequest|\Illuminate\Http\Request $request */
        try {
            $user = $this->userService->assignRoleToUser($id, $request->input('role_id'));

            if ($request->wantsJson()) {
                return response()->json($user);
            }

            return redirect()->route('users.show', $user->id)->with('success', 'Role assigned successfully!');
        } catch (UserNotFoundException $e) {
            Log::warning("UserController - Assign Role: User ID {$id} not found.");
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 404);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (RoleNotFoundException $e) {
            Log::warning("UserController - Assign Role: Role not found: " . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400); // Bad Request for invalid role
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (InvalidInputException $e) {
            Log::warning("UserController - Assign Role: Invalid input: " . $e->getMessage());
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
            Log::error("UserController failed to assign role to user ID {$id}: " . $e->getMessage(), ['request' => $request->all()]);
            if ($request->wantsJson()) {
                return response()->json(['error' => 'An error occurred while assigning the role.'], 500);
            }
            return redirect()->back()->withInput()->with('error', 'An error occurred while assigning the role. Please try again.');
        }
    }

    // Add methods for removing roles, managing permissions directly if needed
}
