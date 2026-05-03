<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerProfileController extends Controller
{
    public function __construct(private FileUploadService $fileUploadService) {}

    public function edit(Request $request)
    {
        $pelanggan = Pelanggan::where('email', $request->user()->email)->first();

        return view('customer.profile', [
            'pelanggan' => $pelanggan,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $pelanggan = Pelanggan::where('email', $user->email)->first();

        $data = $request->validate([
            'nama_pelanggan' => ['required', 'string', 'max:255'],
            'no_ktp' => ['required', 'string', 'size:16', Rule::unique('pelanggan', 'no_ktp')->ignore($pelanggan?->id)],
            'no_telp' => ['required', 'string', 'max:12', 'regex:/^\d{1,12}$/'],
            'alamat' => ['required', 'string'],
            'kota1' => ['nullable', 'string', 'max:100'],
            'propinsi1' => ['nullable', 'string', 'max:100'],
            'kodepos1' => ['nullable', 'string', 'max:12'],
            'foto' => ['nullable', 'image', 'max:2048'],
        ], [
            'no_telp.regex' => 'Nomor telepon hanya boleh berisi angka maksimal 12 digit.',
        ]);

        $data['email'] = $user->email;
        $data['created_by'] = $pelanggan?->created_by ?: $user->id;

        if ($request->hasFile('foto')) {
            if ($pelanggan?->foto) {
                $this->fileUploadService->delete($pelanggan->foto);
            }

            $data['foto'] = $this->fileUploadService->upload($request->file('foto'), 'pelanggan');
        }

        if ($pelanggan) {
            $pelanggan->update($data);
        } else {
            $pelanggan = Pelanggan::create($data);
        }

        return redirect()->route('customer.profile.edit')
            ->with('success', 'Data pribadi berhasil disimpan.');
    }
}
