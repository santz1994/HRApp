<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Direksi', 'description' => 'Read-Only global, Dashboard AI'],
            ['name' => 'HR', 'description' => 'Full CRUD, Import/Export, Absensi'],
            ['name' => 'Admin Department', 'description' => 'Read-Only dept sendiri, Workflow Approval'],
            ['name' => 'IT Developer & Administrator', 'description' => 'Akses penuh sistem & AI'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}