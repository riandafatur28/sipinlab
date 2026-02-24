<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MahasiswaSeeder extends Seeder
{
    public function run(): void
    {
        // Mahasiswa 1
        User::create([
            'name' => 'Rianda Faturrahman',
            'email' => 'e41231605@student.polije.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'mahasiswa',
            'email_verified_at' => now(),
        ]);

        // Mahasiswa 2
        User::create([
            'name' => 'John Doe',
            'email' => 'e41231606@student.polije.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'mahasiswa',
            'email_verified_at' => now(),
        ]);

        // Mahasiswa 3
        User::create([
            'name' => 'Jane Smith',
            'email' => 'e41231607@student.polije.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'mahasiswa',
            'email_verified_at' => now(),
        ]);

        $this->command->info('✅ ' . User::where('role', 'mahasiswa')->count() . ' mahasiswa accounts created!');
    }
}
