<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TechnicianLabSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeder ini akan:
     * 1. Membuat user teknisi untuk setiap lab
     * 2. Mengassign teknisi ke lab tertentu
     * 3. Membuat user ketua lab (Ka Lab)
     */
    public function run(): void
    {
        // Daftar lab dan teknisi yang akan dibuat
        $technicians = [
            [
                'name' => 'Teknisi Mobile',
                'email' => 'teknisi.mobile@polije.ac.id',
                'password' => Hash::make('password123'),
                'role' => 'teknisi',
                'lab_name' => 'Mobile',
                'nip' => '199001012020011001',
                'phone' => '081234567890',
                'prodi' => 'Teknik Informatika',
            ],
            [
                'name' => 'Teknisi MMC',
                'email' => 'teknisi.mmc@polije.ac.id',
                'password' => Hash::make('password123'),
                'role' => 'teknisi',
                'lab_name' => 'Multimedia Cerdas (MMC)',
                'nip' => '199002022020021002',
                'phone' => '081234567891',
                'prodi' => 'Teknik Informatika',
            ],
            [
                'name' => 'Teknisi KSI',
                'email' => 'teknisi.ksi@polije.ac.id',
                'password' => Hash::make('password123'),
                'role' => 'teknisi',
                'lab_name' => 'Komputasi dan Sistem Jaringan (KSI)',
                'nip' => '199003032020031003',
                'phone' => '081234567892',
                'prodi' => 'Teknik Informatika',
            ],
            [
                'name' => 'Teknisi AJK',
                'email' => 'teknisi.ajk@polije.ac.id',
                'password' => Hash::make('password123'),
                'role' => 'teknisi',
                'lab_name' => 'Arsitektur dan Jaringan Komputer (AJK)',
                'nip' => '199004042020041004',
                'phone' => '081234567893',
                'prodi' => 'Teknik Informatika',
            ],
            [
                'name' => 'Teknisi RPL',
                'email' => 'teknisi.rpl@polije.ac.id',
                'password' => Hash::make('password123'),
                'role' => 'teknisi',
                'lab_name' => 'Rekayasa Perangkat Lunak (RPL)',
                'nip' => '199005052020051005',
                'phone' => '081234567894',
                'prodi' => 'Teknik Informatika',
            ],
        ];

        // Create technicians
        foreach ($technicians as $technician) {
            User::updateOrCreate(
                ['email' => $technician['email']],
                $technician
            );
        }

        // Create Ketua Lab (Ka Lab)
        User::updateOrCreate(
            ['email' => 'kalab.ti@polije.ac.id'],
            [
                'name' => 'Radiana Arief Pratama, S.Kom., M.Eng.',
                'email' => 'kalab.ti@polije.ac.id',
                'password' => Hash::make('password123'),
                'role' => 'ketua_lab',
                'nip' => '199310092024061001',
                'phone' => '081234567895',
                'prodi' => 'Teknik Informatika',
                'lab_name' => null, // Ka Lab tidak di-assign ke lab tertentu
            ]
        );

        // Create Admin
        User::updateOrCreate(
            ['email' => 'admin@polije.ac.id'],
            [
                'name' => 'Administrator',
                'email' => 'admin@polije.ac.id',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'nip' => '198501012010011001',
                'phone' => '081234567896',
                'prodi' => 'Teknik Informatika',
                'lab_name' => null,
            ]
        );

        $this->command->info('✅ Teknisi dan Ka Lab seeder completed!');
        $this->command->info('📋 Lab Assignments:');
        $this->command->info('  - Mobile: teknisi.mobile@polije.ac.id');
        $this->command->info('  - MMC: teknisi.mmc@polije.ac.id');
        $this->command->info('  - KSI: teknisi.ksi@polije.ac.id');
        $this->command->info('  - AJK: teknisi.ajk@polije.ac.id');
        $this->command->info('  - RPL: teknisi.rpl@polije.ac.id');
        $this->command->info('  - Ka Lab: kalab.ti@polije.ac.id');
        $this->command->info('  - Admin: admin@polije.ac.id');
        $this->command->info('🔑 Default password: password123');
    }
}