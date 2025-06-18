<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'create posts',
            'edit own posts',
            'delete own posts',
            'create answers',
            'edit own answers',
            'delete own answers',
            'edit any post',
            'delete any post',
            'edit any answer',
            'delete any answer',
            'manage editor picks',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
// Regular user role
        $userRole = Role::firstOrCreate(['name' => 'user']);
        $userRole->givePermissionTo([
            'create posts',
            'edit own posts',
            'delete own posts',
            'create answers',
            'edit own answers',
            'delete own answers',
        ]);

        // Editor role
        $editorRole = Role::firstOrCreate(['name' => 'editor']);
        $editorRole->givePermissionTo([
            'create posts',
            'edit own posts',
            'delete own posts',
            'create answers',
            'edit own answers',
            'delete own answers',
            'edit any post',
            'manage editor picks',
        ]);

        // Assign user role to all existing users who don't have any role
        $usersWithoutRoles = User::doesntHave('roles')->get();
        foreach ($usersWithoutRoles as $user) {
            $user->assignRole('user');
        }

        $this->command->info('Assigned user role to ' . $usersWithoutRoles->count() . ' existing users.');
    }
}
