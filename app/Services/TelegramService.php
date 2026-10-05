<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $token;
    protected string $apiUrl;

    public function __construct()
    {
        $this->token = config('telegram.bot_token', '');
        $this->apiUrl = "https://api.telegram.org/bot{$this->token}";
    }

    public function sendMessage(string $chatId, string $message): bool
    {
        if (empty($this->token) || empty($chatId)) {
            return false;
        }

        try {
            $response = Http::post("{$this->apiUrl}/sendMessage", [
                'chat_id'    => $chatId,
                'text'       => $message,
                'parse_mode' => 'HTML',
            ]);

            if (!$response->successful()) {
                Log::warning('Telegram send failed', [
                    'chat_id' => $chatId,
                    'status'  => $response->status(),
                    'body'    => $response->body(),
                ]);
                return false;
            }

            Log::info('Telegram message sent', ['chat_id' => $chatId]);
            return true;
        } catch (\Exception $e) {
            Log::error('Telegram error: ' . $e->getMessage());
            return false;
        }
    }

    // =========================================================
    // 📥 PENGAJUAN BARU → ke Dosen
    // =========================================================
    public function notifyDosenNewBooking(string $chatId, $booking): bool
    {
        $peminjam = $booking->user;
        $message = "📋 <b>PENGAJUAN PEMINJAMAN LAB BARU</b>\n\n"
            . "Peminjam: <b>{$peminjam->name}</b>\n"
            . "Lab: <b>{$booking->lab_name}</b>\n"
            . "Tanggal: {$booking->booking_date}\n"
            . "Waktu: {$booking->start_time} - {$booking->end_time}\n"
            . "Aktivitas: {$booking->activity}\n\n"
            . "Silakan buka aplikasi SipinLab untuk menyetujui atau menolak.";

        return $this->sendMessage($chatId, $message);
    }

    // =========================================================
    // ✅ DISETUJUI DOSEN → ke Teknisi
    // =========================================================
    public function notifyTeknisiBookingApproved(string $chatId, $booking): bool
    {
        $peminjam = $booking->user;
        $message = "✅ <b>BOOKING DISETUJUI DOSEN</b>\n\n"
            . "Peminjam: <b>{$peminjam->name}</b>\n"
            . "Lab: <b>{$booking->lab_name}</b>\n"
            . "Tanggal: {$booking->booking_date}\n"
            . "Waktu: {$booking->start_time} - {$booking->end_time}\n"
            . "Aktivitas: {$booking->activity}\n\n"
            . "Mohon segera periksa dan setujui di aplikasi SipinLab.";

        return $this->sendMessage($chatId, $message);
    }

    // =========================================================
    // ✅ DISETUJUI TEKNISI → ke Ka Lab
    // =========================================================
    public function notifyKalabBookingApproved(string $chatId, $booking): bool
    {
        $peminjam = $booking->user;
        $message = "✅ <b>BOOKING DISETUJUI TEKNISI</b>\n\n"
            . "Peminjam: <b>{$peminjam->name}</b>\n"
            . "Lab: <b>{$booking->lab_name}</b>\n"
            . "Tanggal: {$booking->booking_date}\n"
            . "Waktu: {$booking->start_time} - {$booking->end_time}\n"
            . "Aktivitas: {$booking->activity}\n\n"
            . "Mohon lakukan konfirmasi final di aplikasi SipinLab.";

        return $this->sendMessage($chatId, $message);
    }

    // =========================================================
    // 🎉 DIKONFIRMASI SEMUA → ke Peminjam
    // =========================================================
    public function notifyPeminjamConfirmed(string $chatId, $booking): bool
    {
        $message = "🎉 <b>BOOKING LAB DIKONFIRMASI!</b>\n\n"
            . "Lab: <b>{$booking->lab_name}</b>\n"
            . "Tanggal: {$booking->booking_date}\n"
            . "Waktu: {$booking->start_time} - {$booking->end_time}\n"
            . "Aktivitas: {$booking->activity}\n\n"
            . "Booking Anda telah disetujui semua pihak. Silakan gunakan lab sesuai jadwal.";

        return $this->sendMessage($chatId, $message);
    }

    // =========================================================
    // ❌ DITOLAK → ke Peminjam
    // =========================================================
    public function notifyPeminjamRejected(string $chatId, $booking, string $reason): bool
    {
        $message = "❌ <b>BOOKING LAB DITOLAK</b>\n\n"
            . "Lab: <b>{$booking->lab_name}</b>\n"
            . "Tanggal: {$booking->booking_date}\n"
            . "Waktu: {$booking->start_time} - {$booking->end_time}\n\n"
            . "Alasan: <i>{$reason}</i>\n\n"
            . "Silakan ajukan ulang dengan perbaikan.";

        return $this->sendMessage($chatId, $message);
    }

    // =========================================================
    // ⏰ REMINDER
    // =========================================================
    public function sendReminderStart(string $chatId, $booking): bool
    {
        $message = "<b>⏰ REMINDER - Lab Akan Dimulai</b>\n\n"
            . "Lab: <b>{$booking->lab_name}</b>\n"
            . "Tanggal: {$booking->booking_date}\n"
            . "Waktu Mulai: <b>{$booking->start_time}</b>\n"
            . "Aktivitas: {$booking->activity}\n\n"
            . "Lab akan digunakan <b>15 menit lagi</b>. Silakan bersiap!";

        return $this->sendMessage($chatId, $message);
    }

    public function sendReminderEnd(string $chatId, $booking): bool
    {
        $message = "<b>⏰ REMINDER - Lab Akan Selesai</b>\n\n"
            . "Lab: <b>{$booking->lab_name}</b>\n"
            . "Tanggal: {$booking->booking_date}\n"
            . "Waktu Selesai: <b>{$booking->end_time}</b>\n"
            . "Aktivitas: {$booking->activity}\n\n"
            . "Sesi lab akan berakhir <b>15 menit lagi</b>. Segera selesaikan kegiatan Anda.";

        return $this->sendMessage($chatId, $message);
    }
}
