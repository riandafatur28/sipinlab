<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Cek apakah admin sudah ada
        if (!User::where('email', 'admin@polije.ac.id')->exists()) {
            User::create([
                'name' => 'Admin Polije',
                'email' => 'admin@polije.ac.id',
                'password' => Hash::make('AdminPolije123!'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);

            $this->command->info('✅ Admin user created: admin@polije.ac.id');
        }
    }
}
