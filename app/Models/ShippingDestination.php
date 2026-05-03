<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingDestination extends Model
{
    protected $table = 'shipping_destinations';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'label',
        'province_name',
        'city_name',
        'district_name',
        'subdistrict_name',
        'zip_code',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'last_seen_at' => 'datetime',
        ];
    }
}
