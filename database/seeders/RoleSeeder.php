<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default roles
        Role::firstOrCreate(
            ['slug' => 'director'],
            [
                'name' => 'Director',
                'description' => 'Director - Can view employee data and reports',
            ]
        );

        Role::firstOrCreate(
            ['slug' => 'hr'],
            [
                'name' => 'HR',
                'description' => 'HR Manager - Can create, read, update, delete employees and manage imports/exports',
            ]
        );
    }
}
