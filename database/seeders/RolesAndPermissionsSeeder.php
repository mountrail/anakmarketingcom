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
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        // Regular user role
        $userRole = Role::create(['name' => 'user']);
        $userRole->givePermissionTo([
            'create posts',
            'edit own posts',
            'delete own posts',
            'create answers',
            'edit own answers',
            'delete own answers',
        ]);

        // Editor role
        $editorRole = Role::create(['name' => 'editor']);
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

        // Admin role
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Assign admin role to a specific user (optional)
        // Uncomment and modify if you want to set an admin during seeding
        /*
        $admin = User::where('email', 'admin@example.com')->first();
        if ($admin) {
            $admin->assignRole('admin');
        }
        */
    }
}
