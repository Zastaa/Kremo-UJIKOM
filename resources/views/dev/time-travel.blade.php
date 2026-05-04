@extends('layouts.app')

@section('title', 'Simulasi Waktu')
@section('page-title', 'Simulasi Waktu')

@section('content')
<div class="card" style="max-width:820px;">
    <div class="card-header">
        <h3><i class="fas fa-clock-rotate-left"></i> Simulasi Waktu Web</h3>
        @if($simulatedAt)
            <form action="{{ route('dev.time-travel.reset') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" class="btn btn-outline btn-sm">
                    <i class="fas fa-rotate-left"></i> Reset
                </button>
            </form>
        @endif
    </div>
    <div class="card-body">
        <div class="alert alert-warning">
            <i class="fas fa-triangle-exclamation"></i>
            Fitur ini hanya aktif di environment local/testing untuk simulasi pembayaran dan jatuh tempo.
        </div>

        <div class="form-grid" style="margin-bottom:22px;">
            <div>
                <strong style="color:var(--text-muted);font-size:0.8rem;">WAKTU ASLI SERVER</strong>
                <p>{{ $realNow->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <strong style="color:var(--text-muted);font-size:0.8rem;">WAKTU YANG DIPAKAI APLIKASI</strong>
                <p>{{ $appNow->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <strong style="color:var(--text-muted);font-size:0.8rem;">STATUS SIMULASI</strong>
                <p>
                    @if($simulatedAt)
                        <span class="badge badge-warning">Aktif</span>
                    @else
                        <span class="badge badge-success">Nonaktif</span>
                    @endif
                </p>
            </div>
        </div>

        <form action="{{ route('dev.time-travel.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="simulated_at">Tanggal dan jam simulasi</label>
                <input
                    type="datetime-local"
                    id="simulated_at"
                    name="simulated_at"
                    class="form-control"
                    value="{{ ($simulatedAt ?: $realNow)->format('Y-m-d\TH:i') }}"
                    required
                >
                <div class="field-hint">Setelah aktif, tombol bayar angsuran mengikuti tanggal simulasi ini pada browser/session yang sama.</div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-play"></i> Aktifkan Simulasi
                </button>
            </div>
        </form>

        <div class="form-section" style="margin-top:22px;">
            <div class="form-section-title"><i class="fas fa-bolt"></i> Shortcut Testing</div>
            <div class="btn-group">
                <form action="{{ route('dev.time-travel.store') }}" method="POST" style="margin:0;">
                    @csrf
                    <input type="hidden" name="preset" value="real_today">
                    <button type="submit" class="btn btn-outline">Hari ini</button>
                </form>
                <form action="{{ route('dev.time-travel.store') }}" method="POST" style="margin:0;">
                    @csrf
                    <input type="hidden" name="preset" value="tomorrow">
                    <button type="submit" class="btn btn-outline">Besok</button>
                </form>
                <form action="{{ route('dev.time-travel.store') }}" method="POST" style="margin:0;">
                    @csrf
                    <input type="hidden" name="preset" value="plus_15">
                    <button type="submit" class="btn btn-outline">+15 Hari</button>
                </form>
                @if($nextAngsuran?->payable_from)
                    <form action="{{ route('dev.time-travel.store') }}" method="POST" style="margin:0;">
                        @csrf
                        <input type="hidden" name="preset" value="next_payable">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-credit-card"></i> H-15 Angsuran Berikutnya
                        </button>
                    </form>
                    <a href="{{ route('angsuran.show', $nextAngsuran) }}" class="btn btn-info">
                        <i class="fas fa-arrow-right"></i> Buka Angsuran
                    </a>
                @endif
            </div>

            @if($nextAngsuran)
                <div style="margin-top:16px;color:var(--text-muted);font-size:0.875rem;">
                    Angsuran berikutnya: ke-{{ $nextAngsuran->angsuran_ke }},
                    jatuh tempo {{ $nextAngsuran->tgl_jatuh_tempo?->format('d/m/Y') ?? '-' }},
                    bisa dibayar mulai {{ $nextAngsuran->payable_from?->format('d/m/Y') ?? '-' }}.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
