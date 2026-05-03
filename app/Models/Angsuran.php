<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Angsuran extends Model
{
    protected $table = 'angsuran';

    public const CUSTOMER_PAYMENT_WINDOW_DAYS = 15;

    protected $fillable = [
        'id_kredit', 'tgl_bayar', 'tgl_jatuh_tempo', 'angsuran_ke',
        'total_bayar', 'status', 'keterangan',
        'snap_token', 'payment_type',
    ];

    protected function casts(): array
    {
        return [
            'tgl_bayar' => 'date',
            'tgl_jatuh_tempo' => 'date',
            'total_bayar' => 'double',
        ];
    }

    public function kredit(): BelongsTo
    {
        return $this->belongsTo(Kredit::class, 'id_kredit');
    }

    public function getPayableFromAttribute(): ?Carbon
    {
        return $this->tgl_jatuh_tempo?->copy()->subDays(self::CUSTOMER_PAYMENT_WINDOW_DAYS)->startOfDay();
    }

    public function getIsWithinPaymentWindowAttribute(): bool
    {
        return $this->payable_from !== null && now()->startOfDay()->greaterThanOrEqualTo($this->payable_from);
    }

    public function getHasUnpaidPreviousInstallmentsAttribute(): bool
    {
        return static::where('id_kredit', $this->id_kredit)
            ->where('angsuran_ke', '<', $this->angsuran_ke)
            ->where('status', '!=', 'Lunas')
            ->exists();
    }

    public function getIsCustomerPayableAttribute(): bool
    {
        return $this->status === 'Belum Bayar'
            && $this->is_down_payment_paid
            && $this->is_within_payment_window
            && ! $this->has_unpaid_previous_installments;
    }

    public function getCustomerPaymentLockReasonAttribute(): ?string
    {
        if ($this->status !== 'Belum Bayar') {
            return null;
        }

        if (! $this->is_down_payment_paid) {
            return 'Selesaikan pembayaran DP dan ongkir terlebih dahulu.';
        }

        if ($this->has_unpaid_previous_installments) {
            return 'Bayar angsuran sebelumnya terlebih dahulu.';
        }

        if (! $this->is_within_payment_window) {
            return 'Bisa dibayar mulai ' . ($this->payable_from?->format('d/m/Y') ?? 'H-15 jatuh tempo') . '.';
        }

        return null;
    }

    public function getIsDownPaymentPaidAttribute(): bool
    {
        $this->loadMissing('kredit.pengajuanKredit');

        return (bool) $this->kredit?->pengajuanKredit?->is_down_payment_paid;
    }
}
