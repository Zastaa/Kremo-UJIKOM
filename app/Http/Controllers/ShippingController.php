<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Province;
use App\Services\RajaOngkirService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShippingController extends Controller
{
    public function __construct(private RajaOngkirService $rajaOngkir) {}

    public function provinces()
    {
        $provinces = Province::orderBy('name')->get();
        if ($provinces->isEmpty()) {
            $data = $this->rajaOngkir->getProvinces();
            return response()->json($data);
        }
        return response()->json($provinces);
    }

    public function cities(int $provinceId)
    {
        $cities = City::where('province_id', $provinceId)->orderBy('name')->get();
        if ($cities->isEmpty()) {
            $data = $this->rajaOngkir->getCities($provinceId);
            return response()->json($data);
        }
        return response()->json($cities);
    }

    public function calculateCost(Request $request)
    {
        $request->validate([
            'origin' => 'nullable|integer',
            'destination' => 'required|integer',
            'weight' => 'required|integer|min:1',
            'courier' => ['nullable', 'string', Rule::in(array_keys($this->rajaOngkir->couriers()))],
            'price' => 'nullable|string|in:lowest,highest',
        ]);

        $origin = $request->integer('origin') ?: (int) config('rajaongkir.origin_destination_id');

        if (! $origin) {
            return response()->json([
                'success' => false,
                'message' => 'Origin RajaOngkir belum diatur.',
                'data' => [],
            ], 422);
        }

        $results = $this->rajaOngkir->calculateDomesticCost(
            $origin,
            $request->integer('destination'),
            $request->integer('weight'),
            $request->input('courier') ?: null,
            $request->input('price', 'lowest')
        );

        if ($results === []) {
            return response()->json([
                'success' => false,
                'message' => $this->rajaOngkir->lastCostError()
                    ?: 'Ongkir tidak tersedia dari RajaOngkir untuk rute ini.',
                'data' => [],
            ], 422);
        }

        return response()->json(['success' => true, 'data' => $results]);
    }

    public function destinations(Request $request)
    {
        $request->validate([
            'search' => 'required|string|min:2|max:80',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->rajaOngkir->searchDomesticDestination(
                $request->input('search'),
                $request->integer('limit') ?: 15
            ),
        ]);
    }

    public function syncRegions()
    {
        $result = $this->rajaOngkir->syncToDatabase();
        return back()->with('success', "Cache shipping siap. {$result['provinces']} provinsi, {$result['cities']} kota, {$result['destinations']} destination tersimpan.");
    }
}
