<?php

namespace App\Services;

use App\Models\Angsuran;
use App\Models\Kredit;
use App\Models\PengajuanKredit;
use Carbon\Carbon;

class AngsuranService
{
    /**
     * Generate installment schedule for a kredit.
     */
    public function generateAngsuran(Kredit $kredit, PengajuanKredit $pengajuan): void
    {
        $jenisCicilan = $pengajuan->jenisCicilan;
        $lamaCicilan = $jenisCicilan->lama_cicilan;
        $cicilanPerbulan = $pengajuan->cicilan_perbulan;

        for ($i = 1; $i <= $lamaCicilan; $i++) {
            Angsuran::create([
                'id_kredit' => $kredit->id,
                'angsuran_ke' => $i,
                'total_bayar' => $cicilanPerbulan,
                'tgl_jatuh_tempo' => Carbon::parse($kredit->tgl_mulai_kredit)->addMonths($i),
                'tgl_bayar' => null,
                'status' => 'Belum Bayar',
            ]);
        }
    }

    /**
     * Pay an installment.
     */
    public function bayarAngsuran(Angsuran $angsuran): Angsuran
    {
        if ($angsuran->status === 'Lunas') {
            return $angsuran;
        }

        $angsuran->update([
            'tgl_bayar' => Carbon::now(),
            'status' => 'Lunas',
        ]);

        // Update sisa_kredit on the kredit
        $kredit = $angsuran->kredit;
        $kredit->sisa_kredit = $kredit->sisa_kredit - $angsuran->total_bayar;
        if ($kredit->sisa_kredit <= 0) {
            $kredit->sisa_kredit = 0;
            $kredit->status_kredit = 'Lunas';
            $kredit->tgl_selesai_kredit = Carbon::now();
        }
        $kredit->save();

        return $angsuran;
    }
}
