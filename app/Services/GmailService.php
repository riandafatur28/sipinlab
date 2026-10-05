<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GmailService
{
    private string $clientId;
    private string $clientSecret;
    private string $refreshToken;
    private string $fromEmail;
    private string $fromName;

    public function __construct()
    {
        $this->clientId     = config('gmail.client_id', '');
        $this->clientSecret = config('gmail.client_secret', '');
        $this->refreshToken = config('gmail.refresh_token', '');
        $this->fromEmail    = config('gmail.from_email', '');
        $this->fromName     = config('gmail.from_name', 'SipinLab');
    }

    private function getAccessToken(): ?string
    {
        $response = Http::post('https://oauth2.googleapis.com/token', [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $this->refreshToken,
            'grant_type'    => 'refresh_token',
        ]);

        if (!$response->successful()) {
            Log::error('Gmail API: gagal ambil access token', ['body' => $response->body()]);
            return null;
        }

        return $response->json('access_token');
    }

    public function send(string $toEmail, string $toName, string $subject, string $htmlBody): bool
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) return false;

        $raw = implode("\r\n", [
            "From: {$this->fromName} <{$this->fromEmail}>",
            "To: {$toName} <{$toEmail}>",
            "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=",
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=UTF-8",
            "Content-Transfer-Encoding: base64",
            "",
            chunk_split(base64_encode($htmlBody)),
        ]);

        $encoded = rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');

        $response = Http::withToken($accessToken)
            ->post('https://gmail.googleapis.com/gmail/v1/users/me/messages/send', [
                'raw' => $encoded,
            ]);

        if (!$response->successful()) {
            Log::error('Gmail API: gagal kirim email', [
                'to'     => $toEmail,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return false;
        }

        Log::info('Gmail API: email terkirim', ['to' => $toEmail, 'subject' => $subject]);
        return true;
    }
}
