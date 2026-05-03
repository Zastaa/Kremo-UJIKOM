<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asuransi extends Model
{
    protected $table = 'asuransi';

    protected $fillable = [
        'nama_perusahaan_asuransi', 'nama_asuransi',
        'margin_asuransi', 'no_rekening', 'url_logo',
    ];

    public function pengajuanKredit(): HasMany
    {
        return $this->hasMany(PengajuanKredit::class, 'id_asuransi');
    }
}
