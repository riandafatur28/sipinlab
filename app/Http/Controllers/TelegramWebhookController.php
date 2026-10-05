<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function __construct(protected TelegramService $telegram) {}

    public function handle(Request $request)
    {
        // Validasi secret token agar hanya Telegram yang bisa akses endpoint ini
        $secret = config('telegram.webhook_secret');
        if ($secret && $request->header('X-Telegram-Bot-Api-Secret-Token') !== $secret) {
            return response()->json(['ok' => false], 403);
        }

        $data = $request->all();
        Log::info('Telegram webhook received', ['chat_id' => $data['message']['chat']['id'] ?? null]);

        $message = $data['message'] ?? $data['edited_message'] ?? null;
        if (!$message) {
            return response()->json(['ok' => true]);
        }

        $chatId = $message['chat']['id'] ?? null;
        $text   = trim($message['text'] ?? '');

        if (!$chatId || !$text) {
            return response()->json(['ok' => true]);
        }

        if (str_starts_with($text, '/start') || str_starts_with($text, '/daftar')) {
            $this->handleDaftar($chatId, $text);
        } elseif ($text === '/bantuan' || $text === '/help') {
            $this->handleBantuan($chatId);
        } elseif ($text === '/status') {
            $this->handleStatus($chatId);
        }

        return response()->json(['ok' => true]);
    }

    private function handleDaftar(string $chatId, string $text): void
    {
        // Ambil email dari teks: /daftar email@domain.com atau /start email@domain.com
        $parts = preg_split('/\s+/', $text, 2);
        $email = isset($parts[1]) ? trim($parts[1]) : null;

        if (!$email) {
            $this->telegram->sendMessage($chatId,
                "Halo! Selamat datang di <b>SipinLab Bot</b> 🤖\n\n"
                . "Untuk menghubungkan akun Anda, kirim:\n"
                . "<code>/daftar email_anda@polije.ac.id</code>\n\n"
                . "Contoh:\n"
                . "<code>/daftar e41231252@student.polije.ac.id</code>"
            );
            return;
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->telegram->sendMessage($chatId,
                "❌ Email <b>{$email}</b> tidak ditemukan di sistem SipinLab.\n\n"
                . "Pastikan email yang Anda masukkan sudah terdaftar."
            );
            return;
        }

        // Cek jika chat ID ini sudah dipakai akun lain
        $existing = User::where('telegram_chat_id', $chatId)
            ->where('id', '!=', $user->id)
            ->first();

        if ($existing) {
            $existing->update(['telegram_chat_id' => null]);
        }

        $user->update(['telegram_chat_id' => (string) $chatId]);

        Log::info('Telegram chat ID linked', [
            'user_id' => $user->id,
            'email'   => $user->email,
            'chat_id' => $chatId,
        ]);

        $this->telegram->sendMessage($chatId,
            "✅ <b>Berhasil terhubung!</b>\n\n"
            . "Akun: <b>{$user->name}</b>\n"
            . "Role: <b>" . ucfirst($user->role) . "</b>\n\n"
            . "Anda akan menerima notifikasi:\n"
            . "⏰ 15 menit sebelum lab dimulai\n"
            . "⏰ 15 menit sebelum lab selesai"
        );
    }

    private function handleStatus(string $chatId): void
    {
        $user = User::where('telegram_chat_id', (string) $chatId)->first();

        if (!$user) {
            $this->telegram->sendMessage($chatId,
                "❌ Akun Anda belum terhubung.\n\n"
                . "Kirim: <code>/daftar email_anda@polije.ac.id</code>"
            );
            return;
        }

        $this->telegram->sendMessage($chatId,
            "✅ <b>Status Akun</b>\n\n"
            . "Nama: <b>{$user->name}</b>\n"
            . "Email: {$user->email}\n"
            . "Role: <b>" . ucfirst($user->role) . "</b>\n"
            . "Notifikasi: <b>Aktif</b> ✅"
        );
    }

    private function handleBantuan(string $chatId): void
    {
        $this->telegram->sendMessage($chatId,
            "<b>📋 Daftar Perintah SipinLab Bot</b>\n\n"
            . "/daftar <code>email</code> — Hubungkan akun SipinLab\n"
            . "/status — Cek status akun yang terhubung\n"
            . "/bantuan — Tampilkan pesan ini\n\n"
            . "Contoh:\n"
            . "<code>/daftar e41231252@student.polije.ac.id</code>"
        );
    }
}
