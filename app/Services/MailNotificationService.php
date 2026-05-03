<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use App\Models\EmailLog;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailNotificationService
{
    /**
     * Send email via queue (or sync fallback) and log it.
     */
    public function send(
        Mailable $mailable,
        string $toEmail,
        string $subject,
        string $template,
        ?int $userId = null,
        ?string $relatedType = null,
        ?int $relatedId = null,
    ): EmailLog {
        $log = EmailLog::create([
            'user_id' => $userId,
            'to_email' => $toEmail,
            'subject' => $subject,
            'template' => $template,
            'status' => 'queued',
            'related_type' => $relatedType,
            'related_id' => $relatedId,
        ]);

        try {
            if (config('queue.default') !== 'sync') {
                SendEmailJob::dispatch($mailable, $toEmail, $log->id);
            } else {
                Mail::to($toEmail)->send($mailable);
                $log->update(['status' => 'sent', 'sent_at' => now()]);
            }
        } catch (\Exception $e) {
            Log::error('MailNotificationService: ' . $e->getMessage());
            $log->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
        }

        return $log;
    }
}
