<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ScanToken extends Model
{
    protected $table = 'scan_tokens';

    protected $fillable = [
        'token', 'tokenable_type', 'tokenable_id', 'expired_at', 'used_at',
    ];

    protected function casts(): array
    {
        return [
            'expired_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public function tokenable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isExpired(): bool
    {
        return $this->expired_at && $this->expired_at->isPast();
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }
}
