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
        $this->provider = config('whatsapp.provider', 'fonnte');
        $this->setupProvider();
    }

    protected function setupProvider()
    {
        if ($this->provider === 'fonnte') {
            $this->baseUrl = 'https://api.fonnte.com/send'; // ✅ Hapus spasi
            $this->apiKey = config('whatsapp.fonnte.api_key');
        } elseif ($this->provider === 'wablas') {
            $domain = rtrim(config('whatsapp.wablas.domain', 'https://solo.wablas.com'), '/');
            $this->baseUrl = $domain . '/api/send-message';
            $this->apiKey = config('whatsapp.wablas.token');
        } elseif ($this->provider === 'twilio') {
            $accountSid = config('whatsapp.twilio.account_sid');
            $this->baseUrl = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json";
            $this->apiKey = config('whatsapp.twilio.auth_token');
        }
    }

    public function sendOTP($phoneNumber, $otp)
    {
        // Jika development & tidak ada API key, return mock success
        if (app()->environment('local', 'development') && empty($this->apiKey)) {
            Log::info('[DEV MODE] OTP WhatsApp:', ['phone' => $phoneNumber, 'otp' => $otp]);
            return ['status' => true, 'message' => 'Development mode - OTP: ' . $otp];
        }

        $message = $this->generateOTPMessage($otp);

        return match($this->provider) {
            'fonnte' => $this->sendViaFonnte($phoneNumber, $message),
            'wablas' => $this->sendViaWablas($phoneNumber, $message),
            'twilio' => $this->sendViaTwilio($phoneNumber, $message),
            default => throw new \Exception('WhatsApp provider tidak valid: ' . $this->provider),
        };
    }

    protected function generateOTPMessage($otp)
    {
        return "*POLITEKNIK NEGERI JEMBER*\n\n" .
               "Kode OTP Anda: *{$otp}*\n\n" .
               "Kode ini berlaku selama 5 menit.\n" .
               "Jangan berikan kode ini kepada siapapun.\n\n" .
               "Jika Anda tidak meminta reset password, abaikan pesan ini.\n\n" .
               "_Sistem Informasi Akademik Polije_";
    }

    protected function sendViaFonnte($phoneNumber, $message)
    {
        try {
            $response = Http::timeout(30)->withHeaders([
                'Authorization' => $this->apiKey,
            ])->asForm()->post($this->baseUrl, [
                'target' => $phoneNumber,
                'message' => $message,
            ]);

            $data = $response->json();
            return ['status' => ($response->successful() && ($data['status'] ?? false) === true), ...$data];
        } catch (\Exception $e) {
            Log::error('Fonnte Error: ' . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    protected function sendViaWablas($phoneNumber, $message)
    {
        try {
            $response = Http::timeout(30)->post($this->baseUrl, [
                'phone' => $phoneNumber,
                'message' => $message,
                'token' => $this->apiKey,
            ]);

            $data = $response->json();
            return ['status' => $response->successful() && ($data['status'] ?? false) === true, ...$data];
        } catch (\Exception $e) {
            Log::error('Wablas Error: ' . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    protected function sendViaTwilio($phoneNumber, $message)
    {
        try {
            $response = Http::timeout(30)->withBasicAuth(
                config('whatsapp.twilio.account_sid'),
                $this->apiKey
            )->asForm()->post($this->baseUrl, [
                'From' => 'whatsapp:' . config('whatsapp.twilio.from_number'),
                'To' => 'whatsapp:' . $phoneNumber,
                'Body' => $message,
            ]);

            $data = $response->json();
            return ['status' => $response->successful() && isset($data['sid']), ...$data];
        } catch (\Exception $e) {
            Log::error('Twilio Error: ' . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function formatPhoneNumber($phoneNumber)
    {
        if (empty($phoneNumber)) return null;

        $phoneNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);

        if (str_starts_with($phoneNumber, '+')) {
            $phoneNumber = substr($phoneNumber, 1);
        }

        if (str_starts_with($phoneNumber, '0')) {
            $phoneNumber = '62' . substr($phoneNumber, 1);
        } elseif (!str_starts_with($phoneNumber, '62')) {
            $phoneNumber = '62' . $phoneNumber;
        }

        return $phoneNumber;
    }

    /**
     * Cek apakah service siap digunakan
     */
    public function isConfigured(): bool
    {
        if (app()->environment('local', 'development')) {
            return true; // Always true in dev mode
        }
        return !empty($this->apiKey);
    }
}
