<p align="center"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="220" alt="Laravel"></p>

<h1 align="center">SipinLab</h1>

<p align="center">
  <strong>Sistem Peminjaman Laboratorium — Politeknik Negeri Jember</strong><br>
  Booking lab online, alur persetujuan berjenjang, jadwal publik, notifikasi Telegram.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-10-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel 10">
  <img src="https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP 8.1+">
  <img src="https://img.shields.io/badge/Tailwind_CSS-3-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white" alt="Tailwind CSS 3">
  <img src="https://img.shields.io/badge/Alpine.js-3-8CC0D4?style=flat-square&logo=alpinedotjs&logoColor=black" alt="Alpine.js 3">
  <img src="https://img.shields.io/badge/Vite-5-646CFF?style=flat-square&logo=vite&logoColor=white" alt="Vite 5">
  <img src="https://img.shields.io/badge/MySQL-8-4479A1?style=flat-square&logo=mysql&logoColor=white" alt="MySQL 8">
  <img src="https://img.shields.io/badge/Bot-Telegram-26A5E4?style=flat-square&logo=telegram&logoColor=white" alt="Telegram Bot">
</p>

---

## 📌 Tentang

**SipinLab** adalah aplikasi web untuk mengelola peminjaman laboratorium di lingkungan Politeknik Negeri Jember. Mahasiswa mengajukan peminjaman, pengajuan melewati alur persetujuan berjenjang (**dosen → teknisi → ketua lab**), dan setiap perubahan status dikirim notifikasi lewat bot Telegram. Halaman jadwal lab dapat diakses publik tanpa login.

## ✨ Fitur

### 🔓 Publik (tanpa login)
- **Jadwal lab publik** — landing page `/` & `/jadwal` menampilkan jadwal kelas dan peminjaman aktif.

### 🎓 Mahasiswa
- Ajukan peminjaman lab (individu / kelompok)
- Pantau status pengajuan (pending, approved, rejected)
- Cetak / unduh **formulir peminjaman PDF** (DomPDF) setelah disetujui
- Reset password via **OTP** (email)

### 👨‍🏫 Dosen & Staff
- Setujui / tolak pengajuan sesuai peran
- Lihat statistik peminjaman per lab

### 🔧 Teknisi & Ketua Lab
- Persetujuan tahap kedua (teknisi) dan tahap akhir (ketua lab / kalab)
- Kelola jadwal kelas per lab

### ⚙️ Admin
- Manajemen user (mahasiswa, dosen, teknisi, kalab, admin)
- Manajemen lab & jadwal kelas
- Monitoring seluruh peminjaman dengan filter & pencarian

## 🔄 Alur Persetujuan Peminjaman

```
pending ──► approved_dosen ──► approved_teknisi ──► confirmed
    │              │                   │                  │
    └──────────────┴───────────────────┴──── rejected ────┘
```

| Status | Menunggu persetujuan |
|---|---|
| `pending` | Dosen |
| `approved_dosen` | Teknisi |
| `approved_teknisi` | Ketua Lab (Kalab) |
| `confirmed` | Selesai — form PDF dapat diunduh |
| `rejected` / `cancelled` | Ditolak / dibatalkan |

## 🤖 Integrasi

- **Telegram Bot** — webhook (`/telegram/webhook`) untuk daftar akun (`/daftar`), cek status peminjaman (`/status`), dan bantuan (`/bantuan`). Notifikasi otomatis ke `telegram_chat_id` user. Setup via artisan: `php artisan telegram:set-webhook {url}`.
- **WhatsApp & Gmail Service** — notifikasi multi-kanal (fallback aman jika gagal).
- **Google OAuth** — field akun Google siap di database (Laravel Socialite terpasang).
- **DomPDF** — ekspor form peminjaman resmi.
- **OTP Password Reset** — verifikasi kode sebelum reset password.

## 🛠 Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | Laravel 10, PHP 8.1+, Sanctum |
| Frontend | Blade, Tailwind CSS 3, Alpine.js, Vite 5 |
| Database | MySQL 8 |
| Notifikasi | Telegram Bot API, WhatsApp, Gmail (Resend) |
| Ekspor | barryvdh/laravel-dompdf |
| Dev tooling | Laragon, Cloudflare Tunnel (`.start.bat`), Laravel Pint |

## 📂 Struktur Project

```
sipinlab1/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/          # UserManagement, LabManagement, Schedule, ClassSchedule
│   │   ├── Auth/           # Login, OTP reset password
│   │   ├── BookingController.php
│   │   ├── DashboardController.php
│   │   └── TelegramWebhookController.php
│   ├── Models/             # User, Booking, Lab, ClassSchedule
│   ├── Services/           # TelegramService, WhatsAppService, GmailService
│   └── Console/Commands/   # SetTelegramWebhook, SendLabReminders
├── database/
│   ├── migrations/
│   └── seeders/            # Admin, Mahasiswa, TechnicianLab
├── resources/views/        # admin, booking, dashboard, public, emails
└── routes/web.php
```

## 🚀 Instalasi

### Prasyarat
- PHP 8.1+ (ekstensi: openssl, pdo_mysql, mbstring, bcmath, gd)
- Composer
- Node.js 18+ & npm
- MySQL 8
- (Opsional) cloudflared untuk Telegram webhook publik

### Langkah

```bash
# 1. Clone & dependensi
git clone https://github.com/DazaiSan20/sipinlab1.git
cd sipinlab1
composer install
npm install

# 2. Konfigurasi environment
cp .env.example .env
php artisan key:generate
# Edit .env: DB_DATABASE, DB_USERNAME, DB_PASSWORD
# TELEGRAM_BOT_TOKEN, TELEGRAM_WEBHOOK_SECRET (opsional)

# 3. Database
php artisan migrate --seed

# 4. Build aset
npm run build

# 5. Jalankan
php artisan serve
```

### Telegram Webhook

```bash
php artisan telegram:set-webhook https://<url-public-kamu>
```

Untuk development lokal, gunakan Cloudflare Tunnel:

```bash
cloudflared tunnel --url http://localhost:8000
```

> Windows: jalankan `start.bat` — script otomatis start `artisan serve` + cloudflared dan membersihkan cache Laravel.

## 👤 Akun Demo (dari seeder)

| Peran | Email | Password |
|---|---|---|
| Admin | `admin@polije.ac.id` | `AdminPolije123!` |

> Seeder lain membuat akun mahasiswa & teknisi lab — cek `database/seeders/`.

## 🔐 Role-Based Access

| Role | Kemampuan |
|---|---|
| `mahasiswa` | Ajukan & pantau peminjaman |
| `dosen` | Persetujuan tahap 1 |
| `teknisi` | Persetujuan tahap 2 |
| `ketua_lab` / kalab | Persetujuan akhir |
| `admin` | Full akses manajemen |

Akses dilindungi middleware `auth` + `prevent-back`, dengan redirect dashboard otomatis berdasarkan role.

## 📄 License

MIT — bebas dipakai & dimodifikasi.

---

Dibangun untuk kebutuhan pengelolaan laboratorium Politeknik Negeri Jember. Kontribusi & masukan sangat terbuka melalui issue / pull request.
