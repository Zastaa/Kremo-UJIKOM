<?php

namespace App\Http\Controllers;

use App\Models\Angsuran;
use App\Services\AngsuranService;
use Illuminate\Http\Request;

class AngsuranController extends Controller
{
    public function __construct(
        private AngsuranService $angsuranService,
    ) {}

    public function index(Request $request)
    {
        $query = Angsuran::with('kredit.pengajuanKredit.pelanggan')
            ->whereIn('id', function($q) {
                // Hanya ambil angsuran pertama (urutan terkecil) yang belum dibayar untuk setiap kredit
                $q->select(\Illuminate\Support\Facades\DB::raw('MIN(id)'))
                  ->from('angsuran')
                  ->where('status', 'Belum Bayar')
                  ->groupBy('id_kredit');
            });

        $user = $request->user();
        if ($user->role === 'customer') {
            $query->whereHas('kredit.pengajuanKredit.pelanggan', function($q) use ($user) {
                $q->where('email', $user->email);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $angsuran = $query->latest()->paginate(15);
        return view('angsuran.index', compact('angsuran'));
    }

    public function show(Request $request, Angsuran $angsuran)
    {
        $this->abortIfCustomerDoesNotOwnAngsuran($request, $angsuran);

        $angsuran->load('kredit.pengajuanKredit.pelanggan', 'kredit.pengajuanKredit.motor');
        return view('angsuran.show', compact('angsuran'));
    }

    public function bayar(Angsuran $angsuran)
    {
        $this->angsuranService->bayarAngsuran($angsuran);

        return redirect()->back()->with('success', 'Angsuran ke-' . $angsuran->angsuran_ke . ' berhasil dibayar.');
    }
}
