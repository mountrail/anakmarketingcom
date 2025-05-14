<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        $users = User::with('roles')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Show the form for editing user roles.
     */
    public function editRoles(User $user)
    {
        $roles = Role::all();

        return view('admin.users.roles', compact('user', 'roles'));
    }

    /**
     * Update the roles for the specified user.
     */
    public function updateRoles(Request $request, User $user)
    {
        $validated = $request->validate([
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        // Get the role names from the IDs
        $roleIds = $request->input('roles', []);
        $roles = Role::whereIn('id', $roleIds)->pluck('name');

        // Sync the roles
        $user->syncRoles($roles);

        return redirect()->route('admin.users.index')
            ->with('success', 'User roles updated successfully.');
    }
}
