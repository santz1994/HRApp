<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Direksi', 'slug' => 'director', 'description' => 'Read-Only global, Dashboard AI'],
            ['name' => 'HR', 'slug' => 'hr', 'description' => 'Full CRUD, Import/Export, Absensi'],
            ['name' => 'Admin Department', 'slug' => 'admin_department', 'description' => 'Read-Only dept sendiri, Workflow Approval'],
            ['name' => 'IT Developer & Administrator', 'slug' => 'it', 'description' => 'Akses penuh sistem & AI'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role['slug']], $role);
        }
    }
}