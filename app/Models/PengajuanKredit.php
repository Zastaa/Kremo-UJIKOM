<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PengajuanKredit extends Model
{
    protected $table = 'pengajuan_kredit';

    protected $fillable = [
        'tgl_pengajuan_kredit', 'id_pelanggan', 'created_by', 'id_motor',
        'harga_cash', 'dp', 'id_jenis_cicilan', 'id_metode_bayar', 'harga_kredit',
        'id_asuransi', 'biaya_asuransi_perbulan', 'cicilan_perbulan',
        'url_kk', 'url_ktp', 'url_npwp', 'url_slip_gaji', 'url_foto',
        'status_pengajuan', 'keterangan_status_pengajuan',
        'surveyor_id', 'approver_id', 'catatan_survey',
        'dp_payment_status', 'dp_payment_order_id', 'dp_snap_token',
        'dp_paid_amount', 'dp_paid_at',
        'shipping_origin_destination_id', 'shipping_origin_label',
        'shipping_destination_destination_id', 'shipping_destination_label',
        'shipping_package_weight', 'shipping_courier_code',
        'shipping_courier_service', 'shipping_cost', 'shipping_etd',
    ];

    protected function casts(): array
    {
        return [
            'tgl_pengajuan_kredit' => 'date',
            'harga_kredit' => 'double',
            'biaya_asuransi_perbulan' => 'double',
            'cicilan_perbulan' => 'double',
            'dp_paid_at' => 'datetime',
            'shipping_cost' => 'integer',
            'shipping_package_weight' => 'integer',
        ];
    }

    public function getIsDownPaymentPaidAttribute(): bool
    {
        return $this->dp_payment_status === 'Lunas';
    }

    public function getDownPaymentTotalAttribute(): int
    {
        return (int) $this->dp + (int) ($this->shipping_cost ?? 0);
    }

    public function getSelectedShippingServiceAttribute(): string
    {
        return trim(strtoupper((string) $this->shipping_courier_code) . ' ' . (string) $this->shipping_courier_service) ?: '-';
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function motor(): BelongsTo
    {
        return $this->belongsTo(Motor::class, 'id_motor');
    }

    public function jenisCicilan(): BelongsTo
    {
        return $this->belongsTo(JenisCicilan::class, 'id_jenis_cicilan');
    }

    public function metodeBayar(): BelongsTo
    {
        return $this->belongsTo(MetodeBayar::class, 'id_metode_bayar');
    }

    public function asuransi(): BelongsTo
    {
        return $this->belongsTo(Asuransi::class, 'id_asuransi');
    }

    public function surveyor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'surveyor_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function kredit(): HasOne
    {
        return $this->hasOne(Kredit::class, 'id_pengajuan_kredit');
    }

    public function pengiriman(): HasOne
    {
        return $this->hasOne(Pengiriman::class, 'id_pengajuan_kredit');
    }
}
