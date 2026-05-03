<?php

namespace App\Http\Controllers;

use App\Models\MetodeBayar;
use Illuminate\Http\Request;

class MetodeBayarController extends Controller
{
    public function index()
    {
        $metodeBayar = MetodeBayar::latest()->paginate(15);
        return view('metode-bayar.index', compact('metodeBayar'));
    }

    public function create()
    {
        return view('metode-bayar.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'metode_pembayaran' => 'required|string|max:30',
            'tempat_bayar' => 'nullable|string|max:50',
            'no_rekening' => 'nullable|string|max:25',
        ]);

        MetodeBayar::create($data);

        return redirect()->route('metode-bayar.index')->with('success', 'Metode bayar berhasil ditambahkan.');
    }

    public function edit(MetodeBayar $metodeBayar)
    {
        return view('metode-bayar.edit', compact('metodeBayar'));
    }

    public function update(Request $request, MetodeBayar $metodeBayar)
    {
        $data = $request->validate([
            'metode_pembayaran' => 'required|string|max:30',
            'tempat_bayar' => 'nullable|string|max:50',
            'no_rekening' => 'nullable|string|max:25',
        ]);

        $metodeBayar->update($data);

        return redirect()->route('metode-bayar.index')->with('success', 'Metode bayar berhasil diperbarui.');
    }

    public function destroy(MetodeBayar $metodeBayar)
    {
        $metodeBayar->delete();
        return redirect()->route('metode-bayar.index')->with('success', 'Metode bayar berhasil dihapus.');
    }
}
