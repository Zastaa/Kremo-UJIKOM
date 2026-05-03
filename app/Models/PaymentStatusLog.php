<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentStatusLog extends Model
{
    protected $table = 'payment_status_logs';

    public $timestamps = false;

    protected $fillable = [
        'angsuran_id', 'order_id', 'previous_status',
        'new_status', 'midtrans_response', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'midtrans_response' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function angsuran(): BelongsTo
    {
        return $this->belongsTo(Angsuran::class, 'angsuran_id');
    }
}
