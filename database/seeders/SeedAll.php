<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SeedDatabase extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles first
        $this->call(RoleSeeder::class);
        
        // Then seed users
        $this->call(UserSeeder::class);
        
        // Finally seed employees
        $this->call(EmployeeSeeder::class);
    }
}
