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
        Schema::disableForeignKeyConstraints();
        DB::table('roles')->truncate();
        Schema::enableForeignKeyConstraints();

        $adminRole = Role::create(['name' => 'admin']);

        $userRole = Role::create(['name' => 'user']);
        $nutritionistRole = Role::create(['name' => 'nutritionist']);
        $organizationRole = Role::create(['name' => 'organization']);

        $adminRole->givePermissionTo('all');
    }
}
