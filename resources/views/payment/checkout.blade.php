@extends('layouts.app')
@php
    $paymentTitle = $paymentTitle ?? 'Pembayaran Angsuran';
    $paymentSubtitle = $paymentSubtitle ?? 'Selesaikan pembayaran melalui Midtrans.';
    $totalAmount = $totalAmount ?? ($angsuran->total_bayar ?? 0);
    $summaryRows = $summaryRows ?? [
        'Angsuran Ke' => $angsuran->angsuran_ke ?? '-',
        'Motor' => $angsuran->kredit?->pengajuanKredit?->motor?->nama_motor ?? '-',
        'Order ID' => $angsuran->payment_type ?? '-',
    ];
    $syncUrl = $syncUrl ?? route('payment.sync', $angsuran);
    $cancelUrl = $cancelUrl ?? route('angsuran.index');
@endphp

@section('title', 'Checkout Pembayaran')
@section('page-title', $paymentTitle)
@section('content')
<div class="card" style="max-width:600px;margin:0 auto;">
    <div class="card-header text-center">
        <h3><i class="fas fa-credit-card"></i> {{ $paymentTitle }}</h3>
    </div>
    <div class="card-body text-center">
        <div style="margin-bottom:24px;">
            <p style="color:var(--text-muted);font-size:0.9rem;">{{ $paymentSubtitle }}</p>
            <h2 style="font-size:2.5rem;color:var(--accent);font-weight:700;">Rp {{ number_format($totalAmount,0,',','.') }}</h2>
        </div>
        
        <div class="form-grid text-left" style="background:var(--bg-card);padding:20px;border-radius:12px;margin-bottom:24px;">
            @foreach($summaryRows as $label => $value)
                <div style="{{ $loop->last ? 'grid-column:1/-1;' : '' }}">
                    <strong style="color:var(--text-muted);font-size:0.8rem;">{{ strtoupper($label) }}</strong>
                    <p>{{ $value }}</p>
                </div>
            @endforeach
        </div>

        <button id="pay-button" class="btn btn-primary btn-lg" style="width:100%;font-size:1.1rem;padding:16px;"><i class="fas fa-shield-alt"></i> Proses Pembayaran Sekarang</button>
        <a href="{{ $cancelUrl }}" class="btn btn-outline" style="width:100%;margin-top:12px;">Batalkan / Kembali</a>
    </div>
</div>

@push('scripts')
<script src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script type="text/javascript">
    document.getElementById('pay-button').onclick = function(){
        snap.pay('{{ $snapToken }}', {
            onSuccess: function(result){
                Swal.fire({
                    icon: 'success',
                    title: 'Disetujui!',
                    text: 'Terima kasih, mohon tunggu sebentar (Sinkronisasi)...',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    timer: 2000
                }).then(() => {
                    window.location.href = "{{ $syncUrl }}";
                });
            },
            onPending: function(result){
                Swal.fire({
                    icon: 'info',
                    title: 'Menunggu',
                    text: 'Mohon selesaikan pembayaran. Sedang mensinkronisasi...',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    timer: 2000
                }).then(() => {
                    window.location.href = "{{ $syncUrl }}";
                });
            },
            onError: function(result){
                Swal.fire({
                    icon: 'error',
                    title: 'Pembayaran Gagal',
                    text: 'Terjadi kesalahan saat memproses pembayaran.'
                });
            },
            onClose: function(){
                Swal.fire({
                    icon: 'warning',
                    title: 'Dibatalkan',
                    text: 'Anda menutup popup Midtrans sebelum menyelesaikan pembayaran.'
                });
            }
        });
    };
</script>
@endpush
@endsection
