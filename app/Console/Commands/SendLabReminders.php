<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendLabReminders extends Command
{
    protected $signature = 'lab:send-reminders';
    protected $description = 'Kirim notifikasi Telegram 15 menit sebelum lab mulai dan selesai';

    public function handle(TelegramService $telegram): int
    {
        $now = Carbon::now('Asia/Jakarta');
        $target = $now->copy()->addMinutes(15)->format('H:i');
        $today = $now->toDateString();

        $this->info("Cek reminder untuk waktu: {$target} pada {$today}");

        // Reminder 15 menit sebelum mulai
        $startingBookings = Booking::where('status', 'confirmed')
            ->whereDate('booking_date', $today)
            ->whereRaw("LEFT(start_time, 5) = ?", [$target])
            ->with('user')
            ->get();

        foreach ($startingBookings as $booking) {
            $this->sendToBookingParticipants($telegram, $booking, 'start');
        }

        // Reminder 15 menit sebelum selesai
        $endingBookings = Booking::where('status', 'confirmed')
            ->whereDate('booking_date', $today)
            ->whereRaw("LEFT(end_time, 5) = ?", [$target])
            ->with('user')
            ->get();

        foreach ($endingBookings as $booking) {
            $this->sendToBookingParticipants($telegram, $booking, 'end');
        }

        $total = $startingBookings->count() + $endingBookings->count();
        $this->info("Selesai. Total booking diproses: {$total}");

        return Command::SUCCESS;
    }

    private function sendToBookingParticipants(TelegramService $telegram, Booking $booking, string $type): void
    {
        $recipients = collect();

        // Tambahkan pemilik booking
        if ($booking->user && $booking->user->telegram_chat_id) {
            $recipients->push($booking->user);
        }

        // Tambahkan anggota grup jika ada
        if ($booking->isGroupBooking()) {
            $members = $booking->getMembersCollectionAttribute();
            foreach ($members as $member) {
                if ($member->telegram_chat_id && !$recipients->contains('id', $member->id)) {
                    $recipients->push($member);
                }
            }
        }

        foreach ($recipients as $user) {
            try {
                if ($type === 'start') {
                    $telegram->sendReminderStart($user->telegram_chat_id, $booking);
                } else {
                    $telegram->sendReminderEnd($user->telegram_chat_id, $booking);
                }

                Log::info("Telegram reminder ({$type}) sent", [
                    'booking_id' => $booking->id,
                    'user_id'    => $user->id,
                    'chat_id'    => $user->telegram_chat_id,
                ]);
            } catch (\Exception $e) {
                Log::error("Gagal kirim reminder Telegram", [
                    'booking_id' => $booking->id,
                    'user_id'    => $user->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }
    }
}
