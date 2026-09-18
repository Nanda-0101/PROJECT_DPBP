<?php

namespace Database\Seeders;

use App\Models\MitraProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'name' => 'Rangga Pratama', 'email' => 'user@camrent.id',
            'password' => 'user123', 'phone' => '081234567890',
            'role' => 'user', 'status' => 'Aktif',
        ]);

        $mitra = User::create([
            'name' => 'Alpha Rental Studio', 'email' => 'mitra@camrent.id',
            'password' => 'mitra123', 'phone' => '081298765432',
            'role' => 'mitra', 'status' => 'Terverifikasi',
        ]);

        MitraProfile::create([
            'user_id'      => $mitra->id,
            'owner_name'   => 'Andi Wijaya',
            'nik'          => '3201234567890001',
            'category'     => 'Campuran',
            'city'         => 'Jakarta Selatan',
            'bank_account' => 'BCA 1234567890 a.n. Andi Wijaya',
            'verified_at'  => now(),
        ]);

        User::create([
            'name' => 'Dinda Amelia', 'email' => 'dinda@camrent.id',
            'password' => 'dinda123', 'phone' => '081311122233',
            'role' => 'user', 'status' => 'Aktif',
        ]);
    }
}