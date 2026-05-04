<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ApplySimulatedTime;
use App\Models\Angsuran;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DevTimeTravelController extends Controller
{
    public function show(Request $request)
    {
        $this->abortUnlessEnabled();
        $this->abortUnlessAdmin($request);

        return view('dev.time-travel', [
            'simulatedAt' => ApplySimulatedTime::simulatedAt(),
            'realNow' => $this->realNow(),
            'appNow' => now(),
            'nextAngsuran' => $this->nextGlobalAngsuran(),
        ]);
    }

    public function store(Request $request)
    {
        $this->abortUnlessEnabled();
        $this->abortUnlessAdmin($request);

        $data = $request->validate([
            'simulated_at' => 'nullable|date',
            'preset' => 'nullable|in:real_today,tomorrow,plus_15,next_payable',
        ]);

        $simulatedAt = $this->dateFromPreset($request, $data['preset'] ?? null);

        if (! $simulatedAt && ! empty($data['simulated_at'])) {
            $simulatedAt = Carbon::parse($data['simulated_at'], config('app.timezone'));
        }

        if (! $simulatedAt) {
            return back()->with('error', 'Pilih tanggal simulasi terlebih dahulu.');
        }

        ApplySimulatedTime::store($simulatedAt);

        return redirect()
            ->route('dev.time-travel.show')
            ->with('success', 'Tanggal simulasi web diaktifkan: ' . $simulatedAt->format('d/m/Y H:i') . '.');
    }

    public function reset(Request $request)
    {
        $this->abortUnlessEnabled();
        $this->abortUnlessAdmin($request);

        ApplySimulatedTime::clear();

        return redirect()
            ->route('dev.time-travel.show')
            ->with('success', 'Tanggal simulasi web sudah dikembalikan ke waktu asli.');
    }

    private function abortUnlessEnabled(): void
    {
        abort_unless(app()->environment(['local', 'testing']), 404);
    }

    private function abortUnlessAdmin(Request $request): void
    {
        abort_unless($request->user()?->role === 'admin', 403);
    }

    private function dateFromPreset(Request $request, ?string $preset): ?Carbon
    {
        return match ($preset) {
            'real_today' => $this->realNow()->startOfDay(),
            'tomorrow' => $this->realNow()->addDay()->startOfDay(),
            'plus_15' => $this->realNow()->addDays(15)->startOfDay(),
            'next_payable' => $this->nextGlobalAngsuran()?->payable_from,
            default => null,
        };
    }

    private function nextGlobalAngsuran(): ?Angsuran
    {
        return Angsuran::with(['kredit.pengajuanKredit.pelanggan', 'kredit.pengajuanKredit.motor'])
            ->where('status', 'Belum Bayar')
            ->whereHas('kredit.pengajuanKredit', fn ($query) => $query->where('dp_payment_status', 'Lunas'))
            ->orderBy('tgl_jatuh_tempo')
            ->orderBy('angsuran_ke')
            ->first();
    }

    private function realNow(): Carbon
    {
        $timezone = new \DateTimeZone(config('app.timezone'));

        return Carbon::parse((new \DateTimeImmutable('now', $timezone))->format('Y-m-d H:i:s'), $timezone);
    }
}
