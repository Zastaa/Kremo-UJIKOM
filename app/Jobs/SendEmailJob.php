<?php

namespace App\Jobs;

use App\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        private Mailable $mailable,
        private string $toEmail,
        private int $emailLogId,
    ) {}

    public function handle(): void
    {
        try {
            Mail::to($this->toEmail)->send($this->mailable);

            EmailLog::where('id', $this->emailLogId)->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error("SendEmailJob failed for {$this->toEmail}: " . $e->getMessage());

            EmailLog::where('id', $this->emailLogId)->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e; // Re-throw for retry
        }
    }
}
