<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = ['admin', 'employee', 'client'];

        foreach ($roles as $rol) {
            Role::firstOrCreate(['name' => $rol]);
        }
    }

      
    
}
