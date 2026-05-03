<?php

namespace App\Http\Controllers;

use App\Mail\PengajuanStatusChangedMail;
use App\Mail\ShippingStatusMail;
use App\Models\Pengiriman;
use App\Models\PengajuanKredit;
use App\Services\FileUploadService;
use App\Services\MailNotificationService;
use App\Services\RajaOngkirService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PengirimanController extends Controller
{
    public function __construct(
        private FileUploadService $fileUploadService,
        private RajaOngkirService $rajaOngkir,
        private MailNotificationService $mailNotificationService,
    ) {}

    public function index()
    {
        $pengiriman = Pengiriman::with('pengajuanKredit.pelanggan', 'pengajuanKredit.motor')
            ->latest()
            ->paginate(15);

        return view('pengiriman.index', compact('pengiriman'));
    }

    public function create()
    {
        $pengajuanList = PengajuanKredit::where('status_pengajuan', 'Disetujui')
            ->where('dp_payment_status', 'Lunas')
            ->doesntHave('pengiriman')
            ->with(['pelanggan', 'motor'])
            ->get();

        return view('pengiriman.create', [
            'pengajuanList' => $pengajuanList,
            'couriers' => $this->rajaOngkir->couriers(),
            'originDestinationId' => config('rajaongkir.origin_destination_id'),
            'originLabel' => config('rajaongkir.origin_label'),
            'defaultWeight' => config('rajaongkir.default_weight'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_pengajuan_kredit' => 'required|exists:pengajuan_kredit,id',
            'no_invoice' => 'nullable|string',
            'tgl_kirim' => 'nullable|date',
            'nama_kurir' => 'nullable|string|max:30',
            'telpon_kurir' => ['nullable', 'string', 'max:12', 'regex:/^\d{1,12}$/'],
            'receiver_name' => 'nullable|string|max:120',
            'receiver_phone' => ['nullable', 'string', 'max:12', 'regex:/^\d{1,12}$/'],
            'receiver_address' => 'nullable|string',
            'origin_destination_id' => 'nullable|integer',
            'origin_label' => 'nullable|string|max:255',
            'destination_destination_id' => 'nullable|integer',
            'destination_label' => 'nullable|string|max:255',
            'package_weight' => 'nullable|integer|min:1',
            'courier_code' => ['nullable', 'string', Rule::in(array_keys($this->rajaOngkir->couriers()))],
            'courier_service' => 'nullable|string|max:60',
            'shipping_cost' => 'nullable|integer|min:0',
            'shipping_etd' => 'nullable|string|max:80',
            'awb_number' => 'nullable|string|max:80',
            'keterangan' => 'nullable|string',
        ], [
            'telpon_kurir.regex' => 'Telepon kurir hanya boleh berisi angka maksimal 12 digit.',
            'receiver_phone.regex' => 'Telepon penerima hanya boleh berisi angka maksimal 12 digit.',
        ]);

        $pengajuan = PengajuanKredit::with(['pelanggan', 'motor'])
            ->whereKey($data['id_pengajuan_kredit'])
            ->where('status_pengajuan', 'Disetujui')
            ->where('dp_payment_status', 'Lunas')
            ->doesntHave('pengiriman')
            ->firstOrFail();

        $data['status_kirim'] = 'Sedang Dikirim';
        $data['no_invoice'] = $data['no_invoice'] ?? 'INV-' . date('Ymd') . '-' . str_pad(Pengiriman::count() + 1, 3, '0', STR_PAD_LEFT);
        $data['origin_destination_id'] = $pengajuan->shipping_origin_destination_id ?: ($data['origin_destination_id'] ?: config('rajaongkir.origin_destination_id'));
        $data['origin_label'] = $pengajuan->shipping_origin_label ?: ($data['origin_label'] ?: config('rajaongkir.origin_label'));
        $data['receiver_name'] = $data['receiver_name'] ?: $pengajuan->pelanggan?->nama_pelanggan;
        $data['receiver_phone'] = $data['receiver_phone'] ?: $pengajuan->pelanggan?->no_telp;
        $data['receiver_address'] = $data['receiver_address'] ?: $pengajuan->pelanggan?->alamat;
        $data['destination_destination_id'] = $pengajuan->shipping_destination_destination_id ?: ($data['destination_destination_id'] ?? null);
        $data['destination_label'] = $pengajuan->shipping_destination_label ?: ($data['destination_label'] ?? null);
        $data['package_weight'] = $pengajuan->shipping_package_weight ?: ($pengajuan->motor?->shipping_weight_grams ?: (int) config('rajaongkir.default_weight', 125000));
        $data['courier_code'] = $pengajuan->shipping_courier_code ?: ($data['courier_code'] ?? null);
        $data['courier_service'] = $pengajuan->shipping_courier_service ?: ($data['courier_service'] ?? null);
        $data['shipping_cost'] = $pengajuan->shipping_cost ?: ($data['shipping_cost'] ?? null);
        $data['shipping_etd'] = $pengajuan->shipping_etd ?: ($data['shipping_etd'] ?? null);

        if ($request->hasFile('bukti_foto')) {
            $data['bukti_foto'] = $this->fileUploadService->upload($request->file('bukti_foto'), 'pengiriman');
        }

        $pengiriman = Pengiriman::create($data);

        if (filled($pengiriman->courier_code) && filled($pengiriman->courier_service) && filled($pengiriman->shipping_cost)) {
            $pengiriman->shippingRates()->create([
                'courier' => $pengiriman->courier_code,
                'service' => $pengiriman->courier_service,
                'description' => $pengiriman->destination_label,
                'cost' => $pengiriman->shipping_cost,
                'etd' => $pengiriman->shipping_etd,
                'raw_response' => [
                    'origin' => $pengiriman->origin_destination_id,
                    'destination' => $pengiriman->destination_destination_id,
                    'weight' => $pengiriman->package_weight,
                ],
            ]);
        }

        $pengiriman->load(['pengajuanKredit.pelanggan', 'pengajuanKredit.motor']);
        $this->sendShippingNotification($pengiriman, 'Pengiriman motor dibuat', $request->user()->id);

        return redirect()->route('pengiriman.index')->with('success', 'Pengiriman berhasil dibuat.');
    }

    public function show(Pengiriman $pengiriman)
    {
        $pengiriman->load('pengajuanKredit.pelanggan', 'pengajuanKredit.motor', 'shippingRates', 'deliveryVerifiedBy');

        return view('pengiriman.show', [
            'pengiriman' => $pengiriman,
            'couriers' => $this->rajaOngkir->couriers(),
        ]);
    }

    public function updateStatus(Pengiriman $pengiriman, Request $request)
    {
        $request->validate([
            'status_kirim' => 'required|in:Sedang Dikirim,Tiba Di Tujuan',
            'tgl_tiba' => 'nullable|date',
        ]);

        if ($request->status_kirim === 'Tiba Di Tujuan' && $pengiriman->delivery_verification_status !== Pengiriman::VERIFICATION_ACCEPTED) {
            return back()->with('error', 'Pengiriman hanya bisa diselesaikan setelah pelanggan mengirim bukti penerimaan dan admin/marketing memverifikasinya.');
        }

        $pengiriman->update([
            'status_kirim' => $request->status_kirim,
            'tgl_tiba' => $request->status_kirim === 'Tiba Di Tujuan' ? ($request->tgl_tiba ?? now()) : null,
        ]);

        if ($request->status_kirim === 'Tiba Di Tujuan') {
            $pengajuan = $pengiriman->pengajuanKredit;
            if ($pengajuan->status_pengajuan === 'Disetujui') {
                $pengajuan->update(['status_pengajuan' => 'Diterima']);
                $pengajuan->load(['pelanggan', 'motor', 'jenisCicilan', 'metodeBayar', 'asuransi', 'surveyor', 'approver']);
                $this->sendPengajuanStatusNotification($pengajuan, 'Diterima', $request->user()->id);
            }
        }

        $pengiriman->refresh()->load(['pengajuanKredit.pelanggan', 'pengajuanKredit.motor']);
        $this->sendShippingNotification($pengiriman, 'Status pengiriman diperbarui', $request->user()->id);

        return redirect()->route('pengiriman.show', $pengiriman)->with('success', 'Status pengiriman berhasil diperbarui.');
    }

    public function confirmReceived(Pengiriman $pengiriman, Request $request)
    {
        $this->abortIfCustomerDoesNotOwnPengiriman($request, $pengiriman);

        if (! $pengiriman->can_customer_confirm_received) {
            return back()->with('error', 'Pengiriman ini sudah dikonfirmasi atau sedang menunggu verifikasi.');
        }

        $data = $request->validate([
            'customer_received_photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'customer_received_note' => 'nullable|string|max:1000',
        ], [
            'customer_received_photo.required' => 'Bukti foto penerimaan wajib diunggah.',
            'customer_received_photo.image' => 'Bukti penerimaan harus berupa gambar.',
            'customer_received_photo.max' => 'Bukti foto penerimaan maksimal 2 MB.',
        ]);

        if ($pengiriman->customer_received_photo) {
            $this->fileUploadService->delete($pengiriman->customer_received_photo);
        }

        $photoPath = $this->fileUploadService->uploadImageAsWebp(
            $request->file('customer_received_photo'),
            'pengiriman/customer-proof'
        );

        $pengiriman->update([
            'delivery_verification_status' => Pengiriman::VERIFICATION_PENDING,
            'customer_received_at' => now(),
            'customer_received_photo' => $photoPath,
            'customer_received_note' => $data['customer_received_note'] ?? null,
            'delivery_verified_at' => null,
            'delivery_verified_by' => null,
            'delivery_verification_note' => null,
        ]);

        return back()->with('success', 'Bukti penerimaan berhasil dikirim. Admin/marketing akan memverifikasi pengiriman ini.');
    }

    public function verifyReceived(Pengiriman $pengiriman, Request $request)
    {
        $data = $request->validate([
            'delivery_verification_note' => 'nullable|string|max:1000',
        ]);

        if ($pengiriman->delivery_verification_status !== Pengiriman::VERIFICATION_PENDING) {
            return back()->with('error', 'Belum ada bukti penerimaan pelanggan yang menunggu verifikasi.');
        }

        $pengiriman->update([
            'delivery_verification_status' => Pengiriman::VERIFICATION_ACCEPTED,
            'delivery_verified_at' => now(),
            'delivery_verified_by' => $request->user()->id,
            'delivery_verification_note' => $data['delivery_verification_note'] ?? null,
            'status_kirim' => 'Tiba Di Tujuan',
            'tgl_tiba' => $pengiriman->tgl_tiba ?? now(),
            'tracking_status' => null,
            'tracking_delivered' => false,
        ]);

        $pengiriman->refresh()->load(['pengajuanKredit.pelanggan', 'pengajuanKredit.motor']);

        if ($pengiriman->pengajuanKredit?->status_pengajuan === 'Disetujui') {
            $pengiriman->pengajuanKredit->update(['status_pengajuan' => 'Diterima']);
            $pengiriman->pengajuanKredit->load(['pelanggan', 'motor', 'jenisCicilan', 'metodeBayar', 'asuransi', 'surveyor', 'approver']);
            $this->sendPengajuanStatusNotification($pengiriman->pengajuanKredit, 'Diterima', $request->user()->id);
        }

        $this->sendShippingNotification($pengiriman, 'Pengiriman selesai diverifikasi', $request->user()->id);

        return redirect()->route('pengiriman.show', $pengiriman)->with('success', 'Pengiriman berhasil diselesaikan berdasarkan bukti penerimaan pelanggan.');
    }

    public function rejectReceived(Pengiriman $pengiriman, Request $request)
    {
        $data = $request->validate([
            'delivery_verification_note' => 'required|string|max:1000',
        ], [
            'delivery_verification_note.required' => 'Catatan penolakan wajib diisi agar pelanggan tahu yang perlu diperbaiki.',
        ]);

        if ($pengiriman->delivery_verification_status !== Pengiriman::VERIFICATION_PENDING) {
            return back()->with('error', 'Belum ada bukti penerimaan pelanggan yang menunggu verifikasi.');
        }

        $pengiriman->update([
            'delivery_verification_status' => Pengiriman::VERIFICATION_REJECTED,
            'delivery_verified_at' => now(),
            'delivery_verified_by' => $request->user()->id,
            'delivery_verification_note' => $data['delivery_verification_note'],
        ]);

        $pengiriman->refresh()->load(['pengajuanKredit.pelanggan', 'pengajuanKredit.motor']);
        $this->sendShippingNotification($pengiriman, 'Bukti penerimaan perlu diperbaiki', $request->user()->id);

        return redirect()->route('pengiriman.show', $pengiriman)->with('success', 'Bukti penerimaan ditolak. Pelanggan dapat mengirim ulang bukti foto.');
    }

    public function destroy(Pengiriman $pengiriman)
    {
        $this->fileUploadService->delete($pengiriman->bukti_foto);
        $pengiriman->delete();

        return redirect()->route('pengiriman.index')->with('success', 'Pengiriman berhasil dihapus.');
    }

    private function sendShippingNotification(Pengiriman $pengiriman, string $eventLabel, ?int $userId = null): void
    {
        $email = $pengiriman->pengajuanKredit?->pelanggan?->email;

        if (! filled($email)) {
            return;
        }

        $this->mailNotificationService->send(
            mailable: new ShippingStatusMail($pengiriman, $eventLabel),
            toEmail: $email,
            subject: "{$eventLabel} - {$pengiriman->no_invoice}",
            template: 'shipping-status',
            userId: $userId,
            relatedType: Pengiriman::class,
            relatedId: $pengiriman->id,
        );
    }

    private function abortIfCustomerDoesNotOwnPengiriman(Request $request, Pengiriman $pengiriman): void
    {
        if ($request->user()?->role !== 'customer') {
            return;
        }

        $pengiriman->loadMissing('pengajuanKredit.pelanggan');

        abort_unless(
            $pengiriman->pengajuanKredit?->pelanggan?->email === $request->user()->email,
            403,
            'Akses ditolak. Pengiriman ini bukan milik akun Anda.'
        );
    }

    private function sendPengajuanStatusNotification(PengajuanKredit $pengajuan, string $status, ?int $userId = null): void
    {
        $email = $pengajuan->pelanggan?->email;

        if (! filled($email)) {
            return;
        }

        $this->mailNotificationService->send(
            mailable: new PengajuanStatusChangedMail($pengajuan, $status),
            toEmail: $email,
            subject: "Update Status Pengajuan Kredit #{$pengajuan->id} - Kremo",
            template: 'pengajuan-status-changed',
            userId: $userId,
            relatedType: PengajuanKredit::class,
            relatedId: $pengajuan->id,
        );
    }
}
