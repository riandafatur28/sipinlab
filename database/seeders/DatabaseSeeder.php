<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Menjalankan seeder untuk:
     * - TechnicianLabSeeder: Membuat user teknisi per lab dan ketua lab
     */
    public function run(): void
    {
        // ✅ Jalankan seeder untuk teknisi dan ketua lab
        $this->call([
            TechnicianLabSeeder::class,
        ]);

        // Optional: Tampilkan info setelah seeding
        $this->command->info('🎉 Database seeding completed successfully!');
        $this->command->info('📋 User default yang dibuat:');
        $this->command->info('   👨‍🔧 Teknisi Mobile: teknisi.mobile@polije.ac.id / password123');
        $this->command->info('   👨‍🔧 Teknisi MMC: teknisi.mmc@polije.ac.id / password123');
        $this->command->info('   👨‍🔧 Teknisi KSI: teknisi.ksi@polije.ac.id / password123');
        $this->command->info('   👨‍🔧 Teknisi AJK: teknisi.ajk@polije.ac.id / password123');
        $this->command->info('   👨‍🔧 Teknisi RPL: teknisi.rpl@polije.ac.id / password123');
        $this->command->info('   👔 Ka Lab: kalab.ti@polije.ac.id / password123');
        $this->command->info('   ⚙️  Admin: admin@polije.ac.id / password123');
        $this->command->info('🔐 Silakan ganti password setelah login pertama kali!');
    }
}