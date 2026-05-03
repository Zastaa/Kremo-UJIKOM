<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMotorRequest;
use App\Models\JenisMotor;
use App\Models\Motor;
use App\Services\FileUploadService;
use Illuminate\Http\Request;

class MotorController extends Controller
{
    public function __construct(
        private FileUploadService $fileUploadService,
    ) {}

    public function index()
    {
        $motors = Motor::with('jenisMotor')->latest()->paginate(15);
        return view('motors.index', compact('motors'));
    }

    public function create()
    {
        $jenisMotor = JenisMotor::all();
        return view('motors.create', compact('jenisMotor'));
    }

    public function store(StoreMotorRequest $request)
    {
        $data = $request->validated();

        foreach (['foto1', 'foto2', 'foto3'] as $foto) {
            if ($request->hasFile($foto)) {
                $data[$foto] = $this->fileUploadService->uploadImageAsWebp($request->file($foto), 'motor');
            }
        }

        Motor::create($data);

        return redirect()->route('motors.index')->with('success', 'Motor berhasil ditambahkan.');
    }

    public function show(Motor $motor)
    {
        $motor->load('jenisMotor');
        return view('motors.show', compact('motor'));
    }

    public function edit(Motor $motor)
    {
        $jenisMotor = JenisMotor::all();
        return view('motors.edit', compact('motor', 'jenisMotor'));
    }

    public function update(Request $request, Motor $motor)
    {
        $data = $request->validate([
            'nama_motor' => 'required|string|max:100',
            'id_jenis' => 'required|exists:jenis_motor,id',
            'harga_jual' => 'required|integer|min:0',
            'berat_gram' => 'nullable|integer|min:1',
            'deskripsi_motor' => 'nullable|string',
            'warna' => 'nullable|string|max:50',
            'kapasitas_mesin' => 'nullable|string|max:10',
            'tahun_produksi' => 'nullable|integer',
            'foto1' => 'nullable|image|max:2048',
            'foto2' => 'nullable|image|max:2048',
            'foto3' => 'nullable|image|max:2048',
            'stok' => 'required|integer|min:0',
        ]);

        foreach (['foto1', 'foto2', 'foto3'] as $foto) {
            if ($request->hasFile($foto)) {
                $this->fileUploadService->delete($motor->$foto);
                $data[$foto] = $this->fileUploadService->uploadImageAsWebp($request->file($foto), 'motor');
            }
        }

        $motor->update($data);

        return redirect()->route('motors.index')->with('success', 'Motor berhasil diperbarui.');
    }

    public function destroy(Motor $motor)
    {
        foreach (['foto1', 'foto2', 'foto3'] as $foto) {
            $this->fileUploadService->delete($motor->$foto);
        }
        $motor->delete();
        return redirect()->route('motors.index')->with('success', 'Motor berhasil dihapus.');
    }
}
