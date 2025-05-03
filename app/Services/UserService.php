<?php

namespace App\Services;

use App\Interfaces\Repositories\UserRepositoryInterface;
use App\Interfaces\Repositories\RoleRepositoryInterface; // Need Role Repository
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use App\Exceptions\UserNotFoundException;
use App\Exceptions\RoleNotFoundException; // Need a RoleNotFoundException
use App\Exceptions\PermissionDeniedException; // For authorization logic in service (less common)
use App\Exceptions\InvalidInputException; // For business logic validation
use Illuminate\Support\Facades\Log; // For logging errors
use Illuminate\Support\Str; // For random password if needed
use Spatie\Permission\Models\Role; // Assuming Spatie package

/**
 * Service layer for User and Role management business logic.
 * Handles business rules and orchestrates data access via Repository.
 */
class UserService
{
    protected UserRepositoryInterface $userRepository;
    protected RoleRepositoryInterface $roleRepository; // Inject Role Repository

    /**
     * Constructor.
     *
     * @param UserRepositoryInterface $userRepository The user repository interface.
     * @param RoleRepositoryInterface $roleRepository The role repository interface.
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        RoleRepositoryInterface $roleRepository
    ) {
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
    }

    /**
     * Get all users.
     * Business logic: Maybe exclude certain users (e.g., super admins) based on caller's role.
     *
     * @return Collection<int, User>
     */
    public function getAllUsers(): Collection
    {
        // Add business logic filter here if needed
        return $this->userRepository->getAll();
    }

    /**
     * Get a specific user by ID.
     *
     * @param int $id The user ID.
     * @return User
     * @throws UserNotFoundException If the user is not found.
     */
    public function getUserById(int $id): User
    {
        $user = $this->userRepository->findById($id);

        if (!$user) {
            throw new UserNotFoundException("User with ID {$id} not found.");
        }

        // Add business logic here, e.g., prevent user from viewing another super admin
        // if ($user->hasRole('super-admin') && !auth()->user()->hasRole('super-admin')) {
        //      throw new PermissionDeniedException("You cannot view this user.");
        // }

        return $user;
    }

    /**
     * Create a new user.
     * Business logic: Assign default role, check for duplicate email (Repository might handle).
     *
     * @param array $data User data.
     * @return User The created user.
     * @throws \Exception On repository creation failure or business rule violation.
     */
    public function createUser(array $data): User
    {
        // --- Business Logic ---

        // 1. Check if email already exists (Repository might throw exception for unique constraint)
        // $existingUser = $this->userRepository->findByEmail($data['email']);
        // if ($existingUser) {
        //      throw new InvalidInputException("Email '{$data['email']}' is already in use.");
        // }

        // 2. Apply business rules (e.g., set default password if not provided, assign default role)
        // if (!isset($data['password'])) {
        //      $data['password'] = Str::random(10); // Generate random password
        // }


        // --- Call Repository to persist data ---
        try {
            $user = $this->userRepository->create($data);

            // 3. Assign default role after creation (if not handled by data)
            // $defaultRole = $this->roleRepository->findByName('guest'); // Requires method in RoleRepo
            // if ($defaultRole) {
            //      $user->assignRole($defaultRole); // Requires Spatie trait on User model
            // }


            return $user;
        } catch (\Exception $e) {
            Log::error("UserService failed to create user: " . $e->getMessage(), ['data' => $data]);
            throw new \Exception("Failed to create user.", 500, $e);
        }
    }

    /**
     * Update a user.
     * Business logic: Prevent changing certain fields for specific roles, assign/remove roles.
     *
     * @param int $id The user ID.
     * @param array $data Data to update.
     * @return User The updated user.
     * @throws UserNotFoundException If the user is not found.
     * @throws PermissionDeniedException If user is not allowed to update this user.
     * @throws \Exception On repository update failure or business rule violation.
     */
    public function updateUser(int $id, array $data): User
    {
        // --- Business Logic ---

        // 1. Check if user exists
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw new UserNotFoundException("User with ID {$id} not found for update.");
        }

        // 2. Authorization check (less common in Service, usually in Policy/Controller)
        // if (!auth()->user()->can('update', $user)) { // Requires Policy
        //      throw new PermissionDeniedException("You cannot update this user.");
        // }

        // 3. Apply business rules (e.g., prevent changing email if already verified)
        // if (isset($data['email']) && $user->hasVerifiedEmail()) {
        //     throw new InvalidInputException("Cannot change email for a verified user.");
        // }

        // --- Call Repository to update data ---
        try {
            $updatedUser = $this->userRepository->update($id, $data);
            if (!$updatedUser) {
                // This case is handled by findById check above, but good practice to check
                throw new \Exception("Repository failed to update user ID {$id}.", 500);
            }
            return $updatedUser;
        } catch (\Exception $e) {
            Log::error("UserService failed to update user ID {$id}: " . $e->getMessage(), ['data' => $data]);
            throw new \Exception("Failed to update user.", 500, $e);
        }
    }

    /**
     * Delete a user.
     * Business logic: Prevent deleting certain users (e.g., own account, last admin).
     *
     * @param int $id The user ID.
     * @return bool True on successful deletion.
     * @throws UserNotFoundException If the user is not found.
     * @throws PermissionDeniedException If user is not allowed to delete this user.
     * @throws \Exception On repository deletion failure or business rule violation.
     */
    public function deleteUser(int $id): bool
    {
        // --- Business Logic ---

        // 1. Check if user exists
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw new UserNotFoundException("User with ID {$id} not found for deletion.");
        }

        // 2. Authorization check (less common in Service, usually in Policy/Controller)
        // if (!auth()->user()->can('delete', $user)) { // Requires Policy
        //      throw new PermissionDeniedException("You cannot delete this user.");
        // }

        // 3. Prevent deleting own account
        // if (auth()->id() === $user->id) {
        //      throw new InvalidInputException("You cannot delete your own account.");
        // }

        // 4. Prevent deleting the last admin
        // if ($user->hasRole('admin') && $this->userRepository->countAdmins() === 1) { // Requires countAdmins in Repo
        //      throw new InvalidInputException("Cannot delete the last admin user.");
        // }


        // --- Call Repository to delete data ---
        try {
            $deleted = $this->userRepository->delete($id);
            if (!$deleted) {
                throw new \Exception("Repository failed to delete user ID {$id}.", 500);
            }
            return $deleted;
        } catch (\Exception $e) {
            Log::error("UserService failed to delete user ID {$id}: " . $e->getMessage());
            throw new \Exception("Failed to delete user.", 500, $e);
        }
    }

    /**
     * Assign a role to a user.
     * Business logic: Check if role exists, prevent assigning/removing certain roles for certain users.
     *
     * @param int $userId The user ID.
     * @param string|int $role The role name or ID.
     * @return User The updated user.
     * @throws UserNotFoundException If the user is not found.
     * @throws RoleNotFoundException If the role is not found.
     * @throws PermissionDeniedException If user is not allowed to assign this role.
     * @throws \Exception On assignment failure.
     */
    public function assignRoleToUser(int $userId, $role): User
    {
        // --- Business Logic ---

        // 1. Check if user exists
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new UserNotFoundException("User with ID {$userId} not found.");
        }

        // 2. Check if role exists (using RoleRepository)
        $roleModel = is_int($role)
            ? $this->roleRepository->findById($role)
            : $this->roleRepository->findByName($role);

        if (!$roleModel) {
            throw new RoleNotFoundException("Role '{$role}' not found."); // Need RoleNotFoundException
        }

        // 3. Authorization check (e.g., only super admin can assign 'admin' role)
        // if ($roleModel->name === 'admin' && !auth()->user()->hasRole('super-admin')) {
        //      throw new PermissionDeniedException("You cannot assign the 'admin' role.");
        // }

        // 4. Prevent assigning a role the user already has
        // if ($user->hasRole($roleModel->name)) {
        //      throw new InvalidInputException("User already has the role '{$roleModel->name}'.");
        // }


        // --- Perform assignment (requires Spatie trait on User model) ---
        try {
            $user->assignRole($roleModel);
            // Spatie's assignRole method saves the user model automatically
            return $user;
        } catch (\Exception $e) {
            Log::error("UserService failed to assign role '{$role}' to user ID {$userId}: " . $e->getMessage());
            throw new \Exception("Failed to assign role to user.", 500, $e);
        }
    }

    /**
     * Get all available roles.
     *
     * @return Collection<int, Role>
     */
    public function getAllRoles(): Collection
    {
        return $this->roleRepository->getAll();
    }

    // Add methods for removing roles, managing permissions if needed
}
