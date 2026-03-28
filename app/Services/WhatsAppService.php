<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $baseUrl;
    protected $apiKey;
    protected $provider;

    public function __construct()
    {
        $this->provider = config('whatsapp.provider', 'wablas');
        $this->setupProvider();
    }

    protected function setupProvider()
    {
        if ($this->provider === 'wablas') {
            $domain = rtrim(config('whatsapp.wablas.domain'), '/');
            $this->baseUrl = $domain . '/api/send-message';
            $this->apiKey = config('whatsapp.wablas.token');
        }
    }

    // =========================================================
    // 🔑 CORE SEND MESSAGE
    // =========================================================
    public function send($phone, $message)
    {
        $phone = $this->formatPhoneNumber($phone);

        try {
            $response = Http::post($this->baseUrl, [
                'phone' => $phone,
                'message' => $message,
                'token' => $this->apiKey,
            ]);

            Log::info('WA Sent', [
                'phone' => $phone,
                'message' => $message
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('WA Error: ' . $e->getMessage());
            return false;
        }
    }

    // =========================================================
    // 🔐 OTP
    // =========================================================
    public function sendOTP($phone, $otp)
    {
        $message = "*OTP RESET PASSWORD*\n\nKode: *$otp*\nBerlaku 5 menit.";
        return $this->send($phone, $message);
    }

    // =========================================================
    // 📥 BOOKING BARU (KE DOSEN / TEKNISI / KALAB)
    // =========================================================
    public function sendBookingNotification($phone, $booking)
    {
        $link = url('/booking/' . $booking->id);

        $message = "*BOOKING BARU*\n\n"
            . "Nama: {$booking->user->name}\n"
            . "Lab: {$booking->lab_name}\n"
            . "Tanggal: {$booking->booking_date}\n\n"
            . "Silakan ACC:\n$link";

        return $this->send($phone, $message);
    }

    // =========================================================
    // ✅ APPROVAL
    // =========================================================
    public function sendApprovalNotification($phone, $booking, $status)
    {
        $message = "*STATUS BOOKING*\n\n"
            . "Lab: {$booking->lab_name}\n"
            . "Tanggal: {$booking->booking_date}\n"
            . "Status: *$status*";

        return $this->send($phone, $message);
    }

    // =========================================================
    // ⏰ REMINDER
    // =========================================================
    public function sendReminder($phone, $booking, $type = 'start')
    {
        $time = $type === 'start' ? $booking->start_time : $booking->end_time;

        $message = "*REMINDER*\n\n"
            . "Lab: {$booking->lab_name}\n"
            . "Waktu: $time\n\n"
            . "15 menit lagi sesi akan " . ($type === 'start' ? 'dimulai' : 'berakhir');

        return $this->send($phone, $message);
    }

    // =========================================================
    // 📞 FORMAT NOMOR
    // =========================================================
    public function formatPhoneNumber($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            return '62' . substr($phone, 1);
        }

        return $phone;
    }
}
