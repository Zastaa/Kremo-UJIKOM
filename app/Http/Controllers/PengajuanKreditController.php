<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePengajuanKreditRequest;
use App\Mail\PengajuanInvoiceMail;
use App\Mail\PengajuanStatusChangedMail;
use App\Models\Asuransi;
use App\Models\JenisCicilan;
use App\Models\MetodeBayar;
use App\Models\Motor;
use App\Models\Pelanggan;
use App\Models\PengajuanKredit;
use App\Services\FileUploadService;
use App\Services\KreditService;
use App\Services\MailNotificationService;
use App\Services\RajaOngkirService;
use Illuminate\Http\Request;

class PengajuanKreditController extends Controller
{
    public function __construct(
        private KreditService $kreditService,
        private FileUploadService $fileUploadService,
        private MailNotificationService $mailNotificationService,
        private RajaOngkirService $rajaOngkir,
    ) {}

    public function index(Request $request)
    {
        $query = PengajuanKredit::with(['pelanggan', 'motor', 'jenisCicilan', 'asuransi', 'surveyor', 'approver']);

        $user = $request->user();
        if ($user->role === 'customer') {
            $query->whereHas('pelanggan', function($q) use ($user) {
                $q->where('email', $user->email);
            });
        } elseif ($user->role === 'surveyor') {
            $query->where(function ($q) use ($user) {
                $q->where('surveyor_id', $user->id)
                    ->orWhere(function ($q) {
                        $q->whereNull('surveyor_id')
                            ->where('status_pengajuan', 'Menunggu Konfirmasi');
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status_pengajuan', $request->status);
        }

        $pengajuan = $query->latest()->paginate(15);
        return view('pengajuan.index', compact('pengajuan'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $myPelanggan = null;

        if ($user->role === 'customer') {
            $pelanggan = collect();
            $myPelanggan = Pelanggan::where('email', $user->email)->first();

            if (! $myPelanggan) {
                return redirect()->route('customer.profile.edit')
                    ->with('error', 'Lengkapi data pribadi terlebih dahulu sebelum membuat pengajuan kredit.');
            }
        } else {
            $pelanggan = Pelanggan::orderBy('nama_pelanggan')
                ->get(['id', 'nama_pelanggan', 'no_ktp']);
        }

        $motors = Motor::with('jenisMotor')
            ->where('stok', '>', 0)
            ->orderBy('nama_motor')
            ->get();
        $jenisCicilan = JenisCicilan::orderBy('lama_cicilan')->get();
        $metodeBayar = MetodeBayar::orderBy('metode_pembayaran')->get();
        $asuransi = Asuransi::orderBy('nama_asuransi')->get();

        return view('pengajuan.create', compact('pelanggan', 'myPelanggan', 'motors', 'jenisCicilan', 'metodeBayar', 'asuransi'));
    }

    public function store(StorePengajuanKreditRequest $request)
    {
        $data = $request->validated();
        
        $user = $request->user();
        if ($user->role === 'customer') {
            $pelanggan = Pelanggan::where('email', $user->email)->first();

            if (! $pelanggan) {
                return redirect()->route('customer.profile.edit')
                    ->with('error', 'Lengkapi data pribadi terlebih dahulu sebelum membuat pengajuan kredit.');
            }

            $data['id_pelanggan'] = $pelanggan->id;
        }

        $data['tgl_pengajuan_kredit'] = now();
        $data['created_by'] = $user->id;

        // Calculate credit
        $hitungan = $this->kreditService->hitungKredit($data);
        $data = array_merge($data, $hitungan);

        // Upload documents
        $docs = $this->fileUploadService->uploadDocuments($request->allFiles());
        $data = array_merge($data, $docs);

        $pengajuan = PengajuanKredit::create($data);
        $pengajuan->load(['pelanggan', 'motor', 'jenisCicilan', 'metodeBayar', 'asuransi']);

        $this->sendPengajuanInvoice($pengajuan, $request->user()?->id);

        return redirect()->route('pengajuan.index')->with('success', 'Pengajuan kredit berhasil dibuat.');
    }

    public function show(Request $request, PengajuanKredit $pengajuan)
    {
        $this->abortIfCustomerDoesNotOwnPengajuan($request, $pengajuan);
        $this->abortIfSurveyorCannotAccessPengajuan($request, $pengajuan);

        $pengajuan->load(['pelanggan', 'motor.jenisMotor', 'jenisCicilan', 'metodeBayar', 'asuransi', 'surveyor', 'approver', 'kredit.angsuran', 'pengiriman']);

        return view('pengajuan.show', [
            'pengajuan' => $pengajuan,
            'couriers' => $this->rajaOngkir->couriers(),
            'originDestinationId' => config('rajaongkir.origin_destination_id'),
            'originLabel' => config('rajaongkir.origin_label'),
            'defaultWeight' => config('rajaongkir.default_weight'),
        ]);
    }

    public function edit(Request $request, PengajuanKredit $pengajuan)
    {
        $this->abortIfCustomerDoesNotOwnPengajuan($request, $pengajuan);
        abort_if($request->user()?->role === 'surveyor', 403, 'Surveyor hanya dapat melihat dan mengirim hasil survey.');

        $pelanggan = Pelanggan::all();
        $motors = Motor::all();
        $jenisCicilan = JenisCicilan::all();
        $metodeBayar = MetodeBayar::orderBy('metode_pembayaran')->get();
        $asuransi = Asuransi::all();

        return view('pengajuan.edit', compact('pengajuan', 'pelanggan', 'motors', 'jenisCicilan', 'metodeBayar', 'asuransi'));
    }

    public function update(Request $request, PengajuanKredit $pengajuan)
    {
        $this->abortIfCustomerDoesNotOwnPengajuan($request, $pengajuan);
        abort_if($request->user()?->role === 'surveyor', 403, 'Surveyor hanya dapat melihat dan mengirim hasil survey.');

        $data = $request->validate([
            'id_pelanggan' => 'required|exists:pelanggan,id',
            'id_motor' => 'required|exists:motor,id',
            'harga_cash' => 'required|integer|min:0',
            'dp' => 'required|integer|min:0',
            'id_jenis_cicilan' => 'required|exists:jenis_cicilan,id',
            'id_metode_bayar' => 'required|exists:metode_bayar,id',
            'id_asuransi' => 'nullable|exists:asuransi,id',
        ]);

        $hitungan = $this->kreditService->hitungKredit($data);
        $data = array_merge($data, $hitungan);

        $docs = $this->fileUploadService->uploadDocuments($request->allFiles());
        $data = array_merge($data, $docs);

        $pengajuan->update($data);

        return redirect()->route('pengajuan.show', $pengajuan)->with('success', 'Pengajuan kredit berhasil diperbarui.');
    }

    public function destroy(Request $request, PengajuanKredit $pengajuan)
    {
        $this->abortIfCustomerDoesNotOwnPengajuan($request, $pengajuan);
        abort_if($request->user()?->role === 'surveyor', 403, 'Surveyor hanya dapat melihat dan mengirim hasil survey.');

        $pengajuan->delete();
        return redirect()->route('pengajuan.index')->with('success', 'Pengajuan kredit berhasil dihapus.');
    }

    // === WORKFLOW ACTIONS ===

    public function claimSurvey(PengajuanKredit $pengajuan, Request $request)
    {
        $updated = PengajuanKredit::whereKey($pengajuan->id)
            ->whereNull('surveyor_id')
            ->where('status_pengajuan', 'Menunggu Konfirmasi')
            ->update([
                'surveyor_id' => $request->user()->id,
                'status_pengajuan' => 'Diproses',
                'updated_at' => now(),
            ]);

        if (! $updated) {
            return back()->with('error', 'Pengajuan ini sudah diambil surveyor lain atau tidak tersedia untuk survey.');
        }

        $pengajuan = $pengajuan->refresh();
        $pengajuan->load(['pelanggan', 'motor', 'jenisCicilan', 'metodeBayar', 'asuransi', 'surveyor', 'approver']);
        $this->sendPengajuanStatusNotification($pengajuan, 'Diproses', $request->user()->id);

        return redirect()->route('pengajuan.show', $pengajuan)
            ->with('success', 'Pengajuan berhasil diambil. Silakan lakukan survey.');
    }

    public function submitSurvey(PengajuanKredit $pengajuan, Request $request)
    {
        abort_unless(
            $pengajuan->surveyor_id === $request->user()->id && $pengajuan->status_pengajuan === 'Diproses',
            403,
            'Akses ditolak. Pengajuan ini bukan tugas survey Anda.'
        );

        $request->validate(['catatan_survey' => 'nullable|string']);

        $pengajuan->catatan_survey = $request->catatan_survey;
        $this->kreditService->updateStatus($pengajuan, 'Survey');
        $pengajuan->load(['pelanggan', 'motor', 'jenisCicilan', 'metodeBayar', 'asuransi', 'surveyor', 'approver']);
        $this->sendPengajuanStatusNotification($pengajuan, 'Survey', $request->user()->id);

        return redirect()->route('pengajuan.show', $pengajuan)->with('success', 'Hasil survey telah disubmit.');
    }

    public function approve(PengajuanKredit $pengajuan, Request $request)
    {
        if (! $pengajuan->id_metode_bayar) {
            return back()->with('error', 'Metode bayar belum dipilih oleh pelanggan.');
        }

        $this->kreditService->approve($pengajuan, $request->user()->id);
        $pengajuan->refresh()->load(['pelanggan', 'motor', 'jenisCicilan', 'metodeBayar', 'asuransi', 'surveyor', 'approver']);
        $this->sendPengajuanStatusNotification($pengajuan, 'Disetujui', $request->user()->id);

        return redirect()->route('pengajuan.show', $pengajuan)->with('success', 'Pengajuan kredit telah disetujui. Pelanggan perlu membayar DP dan ongkir sebelum pengiriman dibuat.');
    }

    public function reject(PengajuanKredit $pengajuan, Request $request)
    {
        $request->validate(['keterangan' => 'nullable|string']);

        $pengajuan->approver_id = $request->user()->id;
        $this->kreditService->updateStatus($pengajuan, 'Ditolak', $request->keterangan);
        $pengajuan->load(['pelanggan', 'motor', 'jenisCicilan', 'metodeBayar', 'asuransi', 'surveyor', 'approver']);
        $this->sendPengajuanStatusNotification($pengajuan, 'Ditolak', $request->user()->id);

        return redirect()->route('pengajuan.show', $pengajuan)->with('success', 'Pengajuan kredit telah ditolak.');
    }

    private function sendPengajuanInvoice(PengajuanKredit $pengajuan, ?int $userId = null): void
    {
        $email = $pengajuan->pelanggan?->email;

        if (! filled($email)) {
            return;
        }

        $this->mailNotificationService->send(
            mailable: new PengajuanInvoiceMail($pengajuan),
            toEmail: $email,
            subject: "Detail Pengajuan Kredit #{$pengajuan->id} - Kremo",
            template: 'pengajuan-invoice',
            userId: $userId,
            relatedType: PengajuanKredit::class,
            relatedId: $pengajuan->id,
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
