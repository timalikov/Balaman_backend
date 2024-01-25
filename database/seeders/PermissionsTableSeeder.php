<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission; 

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Permission::create(['name' => 'all']);
        Permission::create(['name' => 'view users']);
        Permission::create(['name' => 'create users']);
        Permission::create(['name' => 'edit users']);
        Permission::create(['name' => 'delete users']);
    }
}
