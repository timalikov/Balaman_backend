<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role; 

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

        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);

        $adminRole->givePermissionTo('all');
    }
}
