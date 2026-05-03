<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    protected $table = 'provinces';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = ['id', 'name'];

    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'province_id');
    }
}
