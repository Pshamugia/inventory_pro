<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            'products.view','products.create','products.update','products.delete',
            'purchases.create','sales.create','reports.view',
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        $admin   = Role::firstOrCreate(['name' => 'Admin']);
        $manager = Role::firstOrCreate(['name' => 'Manager']);
        $cashier = Role::firstOrCreate(['name' => 'Cashier']);

        $admin->givePermissionTo($perms);
        $manager->givePermissionTo(['products.view','purchases.create','sales.create','reports.view']);
        $cashier->givePermissionTo(['sales.create']);
    }
}
