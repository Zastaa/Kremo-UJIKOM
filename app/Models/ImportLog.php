<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportLog extends Model
{
    protected $table = 'import_logs';

    protected $fillable = [
        'user_id', 'import_type', 'file_name', 'total_rows',
        'success_rows', 'failed_rows', 'error_log', 'status',
    ];

    protected function casts(): array
    {
        return [
            'error_log' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
