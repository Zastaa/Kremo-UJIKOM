<?php

namespace App\Http\Controllers;

use App\Models\Angsuran;
use App\Models\Kredit;
use App\Models\PengajuanKredit;
use Illuminate\Http\Request;

abstract class Controller
{
    protected function abortIfCustomerDoesNotOwnPengajuan(Request $request, PengajuanKredit $pengajuan): void
    {
        if ($request->user()?->role !== 'customer') {
            return;
        }

        $pengajuan->loadMissing('pelanggan');

        abort_unless(
            $pengajuan->pelanggan?->email === $request->user()->email,
            403,
            'Akses ditolak. Data ini bukan milik akun Anda.'
        );
    }

    protected function abortIfSurveyorCannotAccessPengajuan(Request $request, PengajuanKredit $pengajuan): void
    {
        if ($request->user()?->role !== 'surveyor') {
            return;
        }

        $canAccessOpenSurvey = $pengajuan->surveyor_id === null
            && $pengajuan->status_pengajuan === 'Menunggu Konfirmasi';
        $canAccessOwnSurvey = $pengajuan->surveyor_id === $request->user()->id;

        abort_unless(
            $canAccessOpenSurvey || $canAccessOwnSurvey,
            403,
            'Akses ditolak. Pengajuan ini sudah diambil surveyor lain.'
        );
    }

    protected function abortIfCustomerDoesNotOwnKredit(Request $request, Kredit $kredit): void
    {
        if ($request->user()?->role !== 'customer') {
            return;
        }

        $kredit->loadMissing('pengajuanKredit.pelanggan');

        abort_unless(
            $kredit->pengajuanKredit?->pelanggan?->email === $request->user()->email,
            403,
            'Akses ditolak. Data ini bukan milik akun Anda.'
        );
    }

    protected function abortIfCustomerDoesNotOwnAngsuran(Request $request, Angsuran $angsuran): void
    {
        if ($request->user()?->role !== 'customer') {
            return;
        }

        $angsuran->loadMissing('kredit.pengajuanKredit.pelanggan');

        abort_unless(
            $angsuran->kredit?->pengajuanKredit?->pelanggan?->email === $request->user()->email,
            403,
            'Akses ditolak. Data ini bukan milik akun Anda.'
        );
    }
}
