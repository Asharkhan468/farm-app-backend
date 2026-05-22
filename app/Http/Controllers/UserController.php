<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * List all users (Admin Portal).
     * Requires: view users permission
     */
    public function index(Request $request)
    {
        if (!$request->user()->can('view users')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized',
            ], 403);
        }

        $users = User::with('roles')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data'   => $users,
        ], 200);
    }

    /**
     * Create a new user.
     * Requires: create users permission
     */
    public function store(Request $request)
    {
        if (!$request->user()->can('create users')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized! You do not have permission to create users.',
            ], 403);
        }

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role'     => 'required|exists:roles,name',
        ]);

        // Admins cannot create super admin accounts
        if ($request->role === 'super admin' && !$request->user()->hasRole('super admin')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized! Only a Super Admin can create another Super Admin.',
            ], 403);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->role);

        return response()->json([
            'status'  => 'success',
            'message' => 'User created successfully.',
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
        ], 201);
    }

    /**
     * Update an existing user's name, email, role, and optionally password.
     * Requires: create users permission
     */
    public function update(Request $request, User $user)
    {
        if (!$request->user()->can('create users')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized! You do not have permission to update users.',
            ], 403);
        }

        // Non-super-admins cannot modify a super admin account
        if ($user->hasRole('super admin') && !$request->user()->hasRole('super admin')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized! Cannot modify a Super Admin account.',
            ], 403);
        }

        // Prevent self-modification via this endpoint (use /profile instead)
        if ($user->id === $request->user()->id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Use the /profile endpoint to modify your own account.',
            ], 422);
        }

        $request->validate([
            'name'                  => 'sometimes|string|max:255',
            'email'                 => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password'              => 'sometimes|string|min:8|confirmed',
            'password_confirmation' => 'required_with:password',
            'role'                  => 'sometimes|exists:roles,name',
        ]);

        $data = $request->only(['name', 'email']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        if ($request->filled('role')) {
            // Admins cannot promote to super admin
            if ($request->role === 'super admin' && !$request->user()->hasRole('super admin')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Unauthorized! Only a Super Admin can assign the Super Admin role.',
                ], 403);
            }
            $user->syncRoles([$request->role]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'User updated successfully.',
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
        ]);
    }

    /**
     * Delete a user account.
     * Requires: create users permission
     */
    public function destroy(Request $request, User $user)
    {
        if (!$request->user()->can('create users')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized! You do not have permission to delete users.',
            ], 403);
        }

        // Non-super-admins cannot delete a super admin
        if ($user->hasRole('super admin') && !$request->user()->hasRole('super admin')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized! Cannot delete a Super Admin account.',
            ], 403);
        }

        // Prevent self-deletion
        if ($user->id === $request->user()->id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'You cannot delete your own account.',
            ], 422);
        }

        $user->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'User deleted successfully.',
        ]);
    }
}
