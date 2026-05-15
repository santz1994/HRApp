<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Sesuai Project.md Poin 5G: Login via NIK atau email + password.
     * Format email: example@quty.co.id
     */
    public function run(): void
    {
        $directorRole = Role::where('slug', 'director')->first();
        $hrRole = Role::where('slug', 'hr')->first();
        $itRole = Role::where('slug', 'it')->first();
        $adminDeptRole = Role::where('slug', 'admin_department')->first();

        // Direksi (Read-Only global, Dashboard AI)
        User::firstOrCreate(
            ['email' => 'director@quty.co.id'],
            [
                'name' => 'Director',
                'password' => bcrypt('password'),
                'role_id' => $directorRole->id,
                'nik' => '0000000001',
                'email_verified_at' => now(),
            ]
        );

        // HR (Full CRUD, Import/Export, Manajemen Absensi)
        User::firstOrCreate(
            ['email' => 'hr@quty.co.id'],
            [
                'name' => 'HR Manager',
                'password' => bcrypt('password'),
                'role_id' => $hrRole->id,
                'nik' => '0000000002',
                'email_verified_at' => now(),
            ]
        );

        // IT Developer & Administrator
        User::firstOrCreate(
            ['email' => 'it@quty.co.id'],
            [
                'name' => 'IT Administrator',
                'password' => bcrypt('password'),
                'role_id' => $itRole->id,
                'nik' => '0000000003',
                'email_verified_at' => now(),
            ]
        );

        // Admin Department
        User::firstOrCreate(
            ['email' => 'admindept@quty.co.id'],
            [
                'name' => 'Admin Department',
                'password' => bcrypt('password'),
                'role_id' => $adminDeptRole->id,
                'nik' => '0000000004',
                'email_verified_at' => now(),
            ]
        );
    }
}