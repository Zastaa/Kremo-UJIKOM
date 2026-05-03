<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingRate extends Model
{
    protected $table = 'shipping_rates';

    protected $fillable = [
        'pengiriman_id', 'courier', 'service',
        'description', 'cost', 'etd', 'raw_response',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'double',
            'raw_response' => 'array',
        ];
    }

    public function pengiriman(): BelongsTo
    {
        return $this->belongsTo(Pengiriman::class, 'pengiriman_id');
    }
}
