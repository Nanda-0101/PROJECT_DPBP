<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@camrent.id'],
            [
                'name' => 'Admin CamRent',
                'password' => Hash::make('admin123'),
                'phone' => '081200000000',
                'role' => 'admin',
                'status' => 'Aktif',
            ]
        );
    }
}
