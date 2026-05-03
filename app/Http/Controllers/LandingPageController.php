<?php

namespace App\Http\Controllers;

use App\Models\JenisMotor;
use App\Models\Motor;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function index()
    {
        $motors = Motor::with('jenisMotor')->where('stok', '>', 0)->latest()->take(6)->get();
        $jenisMotor = JenisMotor::withCount('motors')->get();

        return view('landing', compact('motors', 'jenisMotor'));
    }

    public function catalog(Request $request)
    {
        $query = Motor::with('jenisMotor')->where('stok', '>', 0);

        if ($request->filled('search')) {
            $query->where(function ($builder) use ($request) {
                $builder->where('nama_motor', 'like', '%' . $request->search . '%')
                    ->orWhere('warna', 'like', '%' . $request->search . '%')
                    ->orWhere('kapasitas_mesin', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('jenis')) {
            $query->where('id_jenis', $request->integer('jenis'));
        }

        match ($request->input('sort')) {
            'price_low' => $query->orderBy('harga_jual'),
            'price_high' => $query->orderByDesc('harga_jual'),
            default => $query->latest(),
        };

        $motors = $query->paginate(12)->withQueryString();
        $jenisMotor = JenisMotor::all();

        return view('catalog', compact('motors', 'jenisMotor'));
    }

    public function motorDetail(Motor $motor)
    {
        $motor->load('jenisMotor');
        return view('motor-detail', compact('motor'));
    }
}
