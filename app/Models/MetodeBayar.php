<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetodeBayar extends Model
{
    protected $table = 'metode_bayar';

    protected $fillable = [
        'metode_pembayaran', 'tempat_bayar', 'no_rekening', 'url_logo',
    ];

    public function kredits(): HasMany
    {
        return $this->hasMany(Kredit::class, 'id_metode_bayar');
    }

    public function pengajuanKredit(): HasMany
    {
        return $this->hasMany(PengajuanKredit::class, 'id_metode_bayar');
    }
}
