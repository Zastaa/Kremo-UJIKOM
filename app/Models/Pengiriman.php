<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pengiriman extends Model
{
    protected $table = 'pengiriman';

    public const VERIFICATION_UNCONFIRMED = 'Belum Dikonfirmasi';
    public const VERIFICATION_PENDING = 'Menunggu Verifikasi';
    public const VERIFICATION_ACCEPTED = 'Diterima';
    public const VERIFICATION_REJECTED = 'Ditolak';

    protected $fillable = [
        'id_pengajuan_kredit', 'no_invoice', 'tgl_kirim', 'tgl_tiba',
        'status_kirim', 'nama_kurir', 'telpon_kurir',
        'receiver_name', 'receiver_phone', 'receiver_address',
        'origin_destination_id', 'origin_label',
        'destination_destination_id', 'destination_label',
        'package_weight', 'courier_code', 'courier_service',
        'shipping_cost', 'shipping_etd', 'awb_number',
        'tracking_status', 'tracking_delivered', 'tracking_last_checked_at',
        'tracking_payload', 'delivery_verification_status',
        'customer_received_at', 'customer_received_photo', 'customer_received_note',
        'delivery_verified_at', 'delivery_verified_by', 'delivery_verification_note',
        'bukti_foto', 'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tgl_kirim' => 'datetime',
            'tgl_tiba' => 'datetime',
            'tracking_delivered' => 'boolean',
            'tracking_last_checked_at' => 'datetime',
            'tracking_payload' => 'array',
            'customer_received_at' => 'datetime',
            'delivery_verified_at' => 'datetime',
        ];
    }

    public function getDeliveryVerificationBadgeClassAttribute(): string
    {
        return match ($this->delivery_verification_status ?: self::VERIFICATION_UNCONFIRMED) {
            self::VERIFICATION_ACCEPTED => 'badge-success',
            self::VERIFICATION_PENDING => 'badge-info',
            self::VERIFICATION_REJECTED => 'badge-danger',
            default => 'badge-warning',
        };
    }

    public function getCanCustomerConfirmReceivedAttribute(): bool
    {
        return $this->status_kirim !== 'Tiba Di Tujuan'
            && in_array($this->delivery_verification_status ?: self::VERIFICATION_UNCONFIRMED, [
                self::VERIFICATION_UNCONFIRMED,
                self::VERIFICATION_REJECTED,
            ], true);
    }

    public function pengajuanKredit(): BelongsTo
    {
        return $this->belongsTo(PengajuanKredit::class, 'id_pengajuan_kredit');
    }

    public function deliveryVerifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_verified_by');
    }

    public function shippingRates(): HasMany
    {
        return $this->hasMany(ShippingRate::class, 'pengiriman_id');
    }
}
