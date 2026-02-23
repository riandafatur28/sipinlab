<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Mahasiswa Test',
            'email' => 'e41231605@student.polije.ac.id',
            'phone' => '081234567890',
            'password' => Hash::make('password123'),
            'role' => 'mahasiswa',
        ]);

        User::create([
            'name' => 'Dosen Test',
            'email' => 'dosen@polije.ac.id',
            'phone' => '081298765432',
            'password' => Hash::make('password123'),
            'role' => 'dosen',
        ]);
    }
}
