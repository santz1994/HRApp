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
            ['email' => 'director@quty.co.id'],
            [
                'name' => 'Director User',
                'password' => bcrypt('password123'),
                'role_id' => $directorRole->id,
                'nik' => '1234567890123456',
                'email_verified_at' => now(),
            ]
        );

        // Create HR user
        User::firstOrCreate(
            ['email' => 'hr@quty.co.id'],
            [
                'name' => 'HR User',
                'password' => bcrypt('password123'),
                'role_id' => $hrRole->id,
                'nik' => '1234567890123457',
                'email_verified_at' => now(),
            ]
        );
    }
}
