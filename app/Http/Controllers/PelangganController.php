<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePelangganRequest;
use App\Models\Pelanggan;
use App\Services\FileUploadService;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    public function __construct(
        private FileUploadService $fileUploadService,
    ) {}

    public function index()
    {
        $pelanggan = Pelanggan::latest()->paginate(15);
        return view('pelanggan.index', compact('pelanggan'));
    }

    public function create()
    {
        return view('pelanggan.create');
    }

    public function store(StorePelangganRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        if ($request->hasFile('foto')) {
            $data['foto'] = $this->fileUploadService->upload($request->file('foto'), 'pelanggan');
        }

        Pelanggan::create($data);

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function show(Pelanggan $pelanggan)
    {
        $pelanggan->load('pengajuanKredit.motor');
        return view('pelanggan.show', compact('pelanggan'));
    }

    public function edit(Pelanggan $pelanggan)
    {
        return view('pelanggan.edit', compact('pelanggan'));
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        $data = $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'email' => 'nullable|email',
            'no_ktp' => 'required|string|size:16|unique:pelanggan,no_ktp,' . $pelanggan->id,
            'no_telp' => ['required', 'string', 'max:12', 'regex:/^\d{1,12}$/'],
            'alamat' => 'nullable|string',
            'kota1' => 'nullable|string',
            'propinsi1' => 'nullable|string',
            'kodepos1' => 'nullable|string',
        ]);

        if ($request->hasFile('foto')) {
            $this->fileUploadService->delete($pelanggan->foto);
            $data['foto'] = $this->fileUploadService->upload($request->file('foto'), 'pelanggan');
        }

        $pelanggan->update($data);

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil diperbarui.');
    }

    public function destroy(Pelanggan $pelanggan)
    {
        $this->fileUploadService->delete($pelanggan->foto);
        $pelanggan->delete();

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil dihapus.');
    }
}
