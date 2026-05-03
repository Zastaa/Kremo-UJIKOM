<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pelanggan extends Model
{
    protected $table = 'pelanggan';

    protected $fillable = [
        'created_by', 'nama_pelanggan', 'email', 'no_ktp', 'no_telp', 'alamat',
        'kota1', 'propinsi1', 'kodepos1',
        'alamat2', 'kota2', 'propinsi2', 'kodepos2',
        'alamat3', 'kota3', 'propinsi3', 'kodepos3',
        'foto',
    ];

    public function pengajuanKredit(): HasMany
    {
        return $this->hasMany(PengajuanKredit::class, 'id_pelanggan');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
