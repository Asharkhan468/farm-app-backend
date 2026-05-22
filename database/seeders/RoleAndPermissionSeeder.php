<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions banayein
        $permissions = ['create users', 'edit users', 'delete users', 'view users', 'manage app data'];
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Roles banayein
        $superAdmin = Role::create(['name' => 'Super Admin']);
        $admin = Role::create(['name' => 'Admin']);
        $manager = Role::create(['name' => 'Manager']);
        $appUser = Role::create(['name' => 'App User']); // React Native walo ke liye

        // Permissions assign karein
        $superAdmin->givePermissionTo(Permission::all());
        $admin->givePermissionTo(['create users', 'edit users', 'view users', 'manage app data']);
        $manager->givePermissionTo(['view users', 'manage app data']);

        // Ek Default Super Admin User banayein
        $user = User::create([
            'name' => 'Main Super Admin',
            'email' => 'admin@portal.com',
            'password' => Hash::make('password123'),
        ]);

        $user->assignRole('Super Admin');
    }
}