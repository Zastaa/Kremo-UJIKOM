<?php

namespace App\Http\Controllers;

use App\Models\Kredit;
use Illuminate\Http\Request;

class KreditController extends Controller
{
    public function index(Request $request)
    {
        $query = Kredit::with(['pengajuanKredit.pelanggan', 'pengajuanKredit.motor', 'metodeBayar']);

        $user = $request->user();
        if ($user->role === 'customer') {
            $query->whereHas('pengajuanKredit.pelanggan', function($q) use ($user) {
                $q->where('email', $user->email);
            });
        }

        if ($request->filled('status')) {
            $query->where('status_kredit', $request->status);
        }

        $kredits = $query->latest()->paginate(15);
        return view('kredit.index', compact('kredits'));
    }

    public function show(Request $request, Kredit $kredit)
    {
        $this->abortIfCustomerDoesNotOwnKredit($request, $kredit);

        $kredit->load(['pengajuanKredit.pelanggan', 'pengajuanKredit.motor', 'metodeBayar', 'angsuran']);
        return view('kredit.show', compact('kredit'));
    }
}
