<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $directorRole = Role::where('slug', 'director')->first();
        $hrRole = Role::where('slug', 'hr')->first();

        // Create director user
        User::firstOrCreate(
            ['email' => 'director@hrapp.com'],
            [
                'name' => 'Director User',
                'password' => bcrypt('password123'),
                'role_id' => $directorRole->id,
                'email_verified_at' => now(),
            ]
        );

        // Create HR user
        User::firstOrCreate(
            ['email' => 'hr@hrapp.com'],
            [
                'name' => 'HR User',
                'password' => bcrypt('password123'),
                'role_id' => $hrRole->id,
                'email_verified_at' => now(),
            ]
        );
    }
}
