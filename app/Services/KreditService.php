<?php

namespace App\Services;

use App\Models\Kredit;
use App\Models\PengajuanKredit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class KreditService
{
    public function __construct(
        private AngsuranService $angsuranService,
    ) {}

    /**
     * Calculate credit details for a pengajuan.
     */
    public function hitungKredit(array $data): array
    {
        $hargaKredit = $data['harga_cash'] - $data['dp'];
        $jenisCicilan = \App\Models\JenisCicilan::findOrFail($data['id_jenis_cicilan']);
        $marginKredit = $jenisCicilan->margin_kredit / 100;
        $totalKredit = $hargaKredit + ($hargaKredit * $marginKredit);

        $biayaAsuransiPerbulan = 0;
        if (!empty($data['id_asuransi'])) {
            $asuransi = \App\Models\Asuransi::findOrFail($data['id_asuransi']);
            $biayaAsuransiPerbulan = ($hargaKredit * ($asuransi->margin_asuransi / 100)) / $jenisCicilan->lama_cicilan;
        }

        $cicilanPerbulan = ($totalKredit / $jenisCicilan->lama_cicilan) + $biayaAsuransiPerbulan;

        return [
            'harga_kredit' => $totalKredit,
            'biaya_asuransi_perbulan' => round($biayaAsuransiPerbulan),
            'cicilan_perbulan' => round($cicilanPerbulan),
        ];
    }

    /**
     * Update status pengajuan kredit following the workflow.
     */
    public function updateStatus(PengajuanKredit $pengajuan, string $newStatus, ?string $keterangan = null): PengajuanKredit
    {
        $allowedTransitions = [
            'Menunggu Konfirmasi' => ['Diproses', 'Dibatalkan Pembeli', 'Dibatalkan Penjual'],
            'Diproses' => ['Survey', 'Dibatalkan Pembeli', 'Dibatalkan Penjual'],
            'Survey' => ['Disetujui', 'Ditolak', 'Bermasalah'],
            'Disetujui' => ['Diterima', 'Dibatalkan Pembeli'],
            'Ditolak' => [],
            'Dibatalkan Pembeli' => [],
            'Dibatalkan Penjual' => [],
            'Bermasalah' => ['Diproses', 'Ditolak'],
            'Diterima' => [],
        ];

        $currentStatus = $pengajuan->status_pengajuan;
        if (!in_array($newStatus, $allowedTransitions[$currentStatus] ?? [])) {
            throw new \InvalidArgumentException(
                "Transisi status dari '{$currentStatus}' ke '{$newStatus}' tidak diizinkan."
            );
        }

        $pengajuan->status_pengajuan = $newStatus;
        if ($keterangan) {
            $pengajuan->keterangan_status_pengajuan = $keterangan;
        }
        $pengajuan->save();

        return $pengajuan;
    }

    /**
     * Approve a pengajuan kredit: create kredit record + generate angsuran.
     */
    public function approve(PengajuanKredit $pengajuan, int $approverId): Kredit
    {
        return DB::transaction(function () use ($pengajuan, $approverId) {
            $pengajuan->approver_id = $approverId;
            $this->updateStatus($pengajuan, 'Disetujui');

            $jenisCicilan = $pengajuan->jenisCicilan;

            $kredit = Kredit::create([
                'id_pengajuan_kredit' => $pengajuan->id,
                'id_metode_bayar' => $pengajuan->id_metode_bayar,
                'tgl_mulai_kredit' => Carbon::now(),
                'tgl_selesai_kredit' => Carbon::now()->addMonths($jenisCicilan->lama_cicilan),
                'sisa_kredit' => $pengajuan->harga_kredit,
                'status_kredit' => 'Dicicil',
            ]);

            $this->angsuranService->generateAngsuran($kredit, $pengajuan);

            return $kredit;
        });
    }

    /**
     * Mark a kredit as paid off (lunas).
     */
    public function markLunas(Kredit $kredit): void
    {
        $kredit->update([
            'status_kredit' => 'Lunas',
            'sisa_kredit' => 0,
            'tgl_selesai_kredit' => Carbon::now(),
        ]);

        $pengajuan = $kredit->pengajuanKredit;
        $pengajuan->update(['status_pengajuan' => 'Diterima']);
    }
}
