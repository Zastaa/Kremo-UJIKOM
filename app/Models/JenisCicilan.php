<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisCicilan extends Model
{
    protected $table = 'jenis_cicilan';

    protected $fillable = [
        'lama_cicilan', 'margin_kredit',
    ];

    public function pengajuanKredit(): HasMany
    {
        return $this->hasMany(PengajuanKredit::class, 'id_jenis_cicilan');
    }
}
