<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        //  Buscar el rol admin

         $adminRole = Role::where('name', 'admin')->first();

        //  Crear admin
        User::firstOrCreate(
            ['email' => 'admin@admin.com'], // evita duplicados
            [
                'name' => 'Esteban Centurion',
                'password' => 'admin123', //  se hashea solo
                'phone' => '123456789',
                'dni' => '12345678',
                'address' => 'Sin dirección',
                'role_id' => $adminRole->id,
                'is_active' => true,
            ]
        );
    }
}