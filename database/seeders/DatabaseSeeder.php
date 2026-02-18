<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@kopaarena.com',
            'password' => bcrypt('password'),
            'role' => 'superadmin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}
