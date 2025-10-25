<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $u = User::firstOrCreate(
            ['email' => 'admin@inventory.local'],
            ['name' => 'Admin', 'password' => bcrypt('password')]
        );

        // If you've added HasRoles on the User model:
        if (method_exists($u, 'assignRole')) {
            $u->assignRole('Admin');
        }
    }
}
