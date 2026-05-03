<?php

namespace App\Jobs;

use App\Mail\InstallmentReminderMail;
use App\Models\Angsuran;
use App\Models\PaymentReminderLog;
use App\Services\MailNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessInstallmentRemindersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Reminder schedule: negative = before due, positive = after due.
     */
    private const REMINDER_SCHEDULE = [
        'H-7' => -7,
        'H-3' => -3,
        'H-1' => -1,
        'H0' => 0,
        'H+1' => 1,
        'H+3' => 3,
        'H+7' => 7,
    ];

    public function handle(MailNotificationService $mailService): void
    {
        $today = now()->toDateString();

        foreach (self::REMINDER_SCHEDULE as $type => $dayOffset) {
            $targetDate = now()->addDays($dayOffset)->toDateString();

            // Find unpaid installments with this due date
            $angsurans = Angsuran::with(['kredit.pengajuanKredit.pelanggan', 'kredit.pengajuanKredit.motor'])
                ->where('status', 'Belum Bayar')
                ->whereDate('tgl_jatuh_tempo', $targetDate)
                ->get();

            foreach ($angsurans as $angsuran) {
                // Skip if reminder already sent for this type + angsuran
                $alreadySent = PaymentReminderLog::where('angsuran_id', $angsuran->id)
                    ->where('reminder_type', $type)
                    ->whereIn('status', ['sent', 'pending'])
                    ->exists();

                if ($alreadySent) continue;

                $pelanggan = $angsuran->kredit?->pengajuanKredit?->pelanggan;
                if (!$pelanggan || !$pelanggan->email) continue;

                try {
                    $mailable = new InstallmentReminderMail($angsuran, $type);

                    $mailService->send(
                        mailable: $mailable,
                        toEmail: $pelanggan->email,
                        subject: "Reminder Angsuran {$type} - Kremo",
                        template: 'installment-reminder',
                        relatedType: Angsuran::class,
                        relatedId: $angsuran->id,
                    );

                    PaymentReminderLog::create([
                        'kredit_id' => $angsuran->kredit->id,
                        'angsuran_id' => $angsuran->id,
                        'reminder_type' => $type,
                        'due_date' => $angsuran->tgl_jatuh_tempo,
                        'sent_at' => now(),
                        'status' => 'sent',
                    ]);
                } catch (\Exception $e) {
                    Log::error("Reminder {$type} for angsuran #{$angsuran->id}: " . $e->getMessage());

                    PaymentReminderLog::create([
                        'kredit_id' => $angsuran->kredit->id ?? null,
                        'angsuran_id' => $angsuran->id,
                        'reminder_type' => $type,
                        'due_date' => $angsuran->tgl_jatuh_tempo,
                        'status' => 'failed',
                    ]);
                }
            }
        }
    }
}
