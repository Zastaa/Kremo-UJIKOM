<?php

namespace App\Http\Controllers;

use App\Models\Asuransi;
use Illuminate\Http\Request;

class AsuransiController extends Controller
{
    public function index()
    {
        $asuransi = Asuransi::latest()->paginate(15);
        return view('asuransi.index', compact('asuransi'));
    }

    public function create()
    {
        return view('asuransi.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_perusahaan_asuransi' => 'required|string|max:30',
            'nama_asuransi' => 'required|string|max:50',
            'margin_asuransi' => 'required|numeric|min:0',
            'no_rekening' => 'nullable|string',
        ]);

        Asuransi::create($data);

        return redirect()->route('asuransi.index')->with('success', 'Asuransi berhasil ditambahkan.');
    }

    public function edit(Asuransi $asuransi)
    {
        return view('asuransi.edit', compact('asuransi'));
    }

    public function update(Request $request, Asuransi $asuransi)
    {
        $data = $request->validate([
            'nama_perusahaan_asuransi' => 'required|string|max:30',
            'nama_asuransi' => 'required|string|max:50',
            'margin_asuransi' => 'required|numeric|min:0',
            'no_rekening' => 'nullable|string',
        ]);

        $asuransi->update($data);

        return redirect()->route('asuransi.index')->with('success', 'Asuransi berhasil diperbarui.');
    }

    public function destroy(Asuransi $asuransi)
    {
        $asuransi->delete();
        return redirect()->route('asuransi.index')->with('success', 'Asuransi berhasil dihapus.');
    }
}
