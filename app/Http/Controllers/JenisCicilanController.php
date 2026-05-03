<?php

namespace App\Http\Controllers;

use App\Models\JenisCicilan;
use Illuminate\Http\Request;

class JenisCicilanController extends Controller
{
    public function index()
    {
        $jenisCicilan = JenisCicilan::latest()->paginate(15);
        return view('jenis-cicilan.index', compact('jenisCicilan'));
    }

    public function create()
    {
        return view('jenis-cicilan.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'lama_cicilan' => 'required|integer|min:1',
            'margin_kredit' => 'required|numeric|min:0',
        ]);

        JenisCicilan::create($data);

        return redirect()->route('jenis-cicilan.index')->with('success', 'Jenis cicilan berhasil ditambahkan.');
    }

    public function edit(JenisCicilan $jenisCicilan)
    {
        return view('jenis-cicilan.edit', compact('jenisCicilan'));
    }

    public function update(Request $request, JenisCicilan $jenisCicilan)
    {
        $data = $request->validate([
            'lama_cicilan' => 'required|integer|min:1',
            'margin_kredit' => 'required|numeric|min:0',
        ]);

        $jenisCicilan->update($data);

        return redirect()->route('jenis-cicilan.index')->with('success', 'Jenis cicilan berhasil diperbarui.');
    }

    public function destroy(JenisCicilan $jenisCicilan)
    {
        $jenisCicilan->delete();
        return redirect()->route('jenis-cicilan.index')->with('success', 'Jenis cicilan berhasil dihapus.');
    }
}
