<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kredit extends Model
{
    protected $table = 'kredit';

    protected $fillable = [
        'id_pengajuan_kredit', 'id_metode_bayar',
        'tgl_mulai_kredit', 'tgl_selesai_kredit',
        'sisa_kredit', 'status_kredit', 'keterangan_status_kredit',
    ];

    protected function casts(): array
    {
        return [
            'tgl_mulai_kredit' => 'date',
            'tgl_selesai_kredit' => 'date',
            'sisa_kredit' => 'double',
        ];
    }

    public function pengajuanKredit(): BelongsTo
    {
        return $this->belongsTo(PengajuanKredit::class, 'id_pengajuan_kredit');
    }

    public function metodeBayar(): BelongsTo
    {
        return $this->belongsTo(MetodeBayar::class, 'id_metode_bayar');
    }

    public function angsuran(): HasMany
    {
        return $this->hasMany(Angsuran::class, 'id_kredit');
    }
}
