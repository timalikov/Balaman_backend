<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks to avoid constraint violations
        Schema::disableForeignKeyConstraints();
        // Truncate the table
        DB::table('roles')->truncate();
        // Enable foreign key checks
        Schema::enableForeignKeyConstraints();

        $roles = [
            [
                'name' => 'ROLE_USER',
            ],
            [
                'name' => 'ROLE_ADMIN',
            ],
            [
                'name' => 'ROLE_NUTRITIONIST',
            ],
            [
                'name' => 'ROLE_SCHOOL',
            ],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insert([
                'name' => $role['name'],
                'created_at' => now(),
            ]);
        }
    }
}
