<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SetTelegramWebhook extends Command
{
    protected $signature = 'telegram:set-webhook {url? : URL webhook (default: APP_URL)}';
    protected $description = 'Daftarkan webhook URL ke Telegram Bot API';

    public function handle(): int
    {
        $token = config('telegram.bot_token');

        if (empty($token)) {
            $this->error('TELEGRAM_BOT_TOKEN belum diset di .env');
            return Command::FAILURE;
        }

        $url = $this->argument('url') ?? rtrim(config('app.url'), '/') . '/telegram/webhook';

        $this->info("Mendaftarkan webhook: {$url}");

        $payload = [
            'url'             => $url,
            'allowed_updates' => ['message'],
        ];

        $secret = config('telegram.webhook_secret');
        if ($secret) {
            $payload['secret_token'] = $secret;
        }

        $response = Http::post("https://api.telegram.org/bot{$token}/setWebhook", $payload);

        $result = $response->json();

        if ($result['ok'] ?? false) {
            $this->info('✅ Webhook berhasil didaftarkan!');
            $this->line('URL: ' . $url);
        } else {
            $this->error('❌ Gagal: ' . ($result['description'] ?? 'Unknown error'));
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
