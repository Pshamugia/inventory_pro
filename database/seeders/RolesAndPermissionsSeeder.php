<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // If the permission cache was published, clear it
        if (function_exists('app')) {
            app('cache')->forget('spatie.permission.cache');
        }

        $perms = [
            'view dashboard',
            'manage products',
            'manage categories',
            'manage units',
            'manage purchases',
            'manage sales',
            'manage adjustments',
            'manage reports',
            'manage users',
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        $admin   = Role::firstOrCreate(['name' => 'admin']);
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $cashier = Role::firstOrCreate(['name' => 'cashier']);

        // Grant permissions
        $admin->givePermissionTo($perms);

        $manager->givePermissionTo([
            'view dashboard',
            'manage products',
            'manage categories',
            'manage units',
            'manage purchases',
            'manage sales',
            'manage reports',
        ]);

        $cashier->givePermissionTo([
            'view dashboard',
            'manage sales',
        ]);
    }
}
