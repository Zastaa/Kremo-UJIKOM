<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentReminderLog extends Model
{
    protected $table = 'payment_reminder_logs';

    protected $fillable = [
        'user_id', 'kredit_id', 'angsuran_id', 'reminder_type',
        'due_date', 'sent_at', 'status',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'sent_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kredit(): BelongsTo
    {
        return $this->belongsTo(Kredit::class, 'kredit_id');
    }

    public function angsuran(): BelongsTo
    {
        return $this->belongsTo(Angsuran::class, 'angsuran_id');
    }
}
