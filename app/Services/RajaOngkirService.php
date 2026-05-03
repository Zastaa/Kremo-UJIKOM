<?php

namespace App\Services;

use App\Models\City;
use App\Models\Province;
use App\Models\ShippingDestination;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RajaOngkirService
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = (string) config('rajaongkir.api_key', '');
        $this->baseUrl = rtrim((string) config('rajaongkir.base_url', 'https://rajaongkir.komerce.id/api/v1'), '/');
    }

    public function enabled(): bool
    {
        return filled($this->apiKey);
    }

    public function couriers(): array
    {
        return config('rajaongkir.couriers', []);
    }

    public function defaultCourierCodes(): string
    {
        return (string) config('rajaongkir.default_couriers', 'jne:sicepat:jnt:tiki:lion:pos:rex');
    }

    public function searchDomesticDestination(string $search, int $limit = 15, int $offset = 0): array
    {
        $search = trim($search);

        if (Str::length($search) < 2) {
            return [];
        }

        $cacheKey = 'rajaongkir_destination_' . md5(Str::lower($search) . "_{$limit}_{$offset}");

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($search, $limit, $offset) {
            if (! $this->enabled()) {
                return $this->fallbackDestinationSearch($search, $limit);
            }

            try {
                $response = $this->client()
                    ->get($this->endpoint('destination/domestic-destination'), [
                        'search' => $search,
                        'limit' => $limit,
                        'offset' => $offset,
                    ]);

                if (! $response->successful()) {
                    Log::warning('RajaOngkir destination search failed', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);

                    return $this->fallbackDestinationSearch($search, $limit);
                }

                $destinations = collect($response->json('data') ?? [])
                    ->map(fn (array $item) => $this->normalizeDestination($item))
                    ->filter(fn (array $item) => filled($item['id']) && filled($item['label']))
                    ->values()
                    ->all();

                $this->rememberDestinations($destinations);

                return $destinations;
            } catch (\Throwable $e) {
                Log::error('RajaOngkir searchDomesticDestination: ' . $e->getMessage());

                return $this->fallbackDestinationSearch($search, $limit);
            }
        });
    }

    public function calculateDomesticCost(
        int $origin,
        int $destination,
        int $weight,
        ?string $courier = null,
        string $price = 'lowest'
    ): array {
        $courier = $courier ?: $this->defaultCourierCodes();

        if (! $this->enabled()) {
            return $this->fallbackRates($origin, $destination, $weight, $courier);
        }

        try {
            $response = $this->client()
                ->asForm()
                ->post($this->endpoint('calculate/domestic-cost'), [
                    'origin' => $origin,
                    'destination' => $destination,
                    'weight' => $weight,
                    'courier' => $courier,
                    'price' => $price,
                ]);

            if (! $response->successful()) {
                Log::warning('RajaOngkir calculateDomesticCost failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return $this->fallbackRates($origin, $destination, $weight, $courier);
            }

            return collect($response->json('data') ?? [])
                ->map(fn (array $item) => $this->normalizeRate($item))
                ->filter(fn (array $item) => filled($item['code']) && filled($item['service']))
                ->sortBy('cost')
                ->values()
                ->all();
        } catch (\Throwable $e) {
            Log::error('RajaOngkir calculateDomesticCost: ' . $e->getMessage());

            return $this->fallbackRates($origin, $destination, $weight, $courier);
        }
    }

    public function trackWaybill(string $awb, string $courier, ?string $lastPhoneNumber = null): array
    {
        $awb = trim($awb);
        $courier = Str::lower(trim($courier));

        if (! $this->enabled()) {
            return [
                'success' => false,
                'message' => 'API key RajaOngkir belum dikonfigurasi.',
                'data' => null,
                'raw' => null,
            ];
        }

        try {
            $query = http_build_query([
                'awb' => $awb,
                'courier' => $courier,
            ]);

            $payload = array_filter([
                'awb' => $awb,
                'courier' => $courier,
                'last_phone_number' => $lastPhoneNumber,
            ], fn ($value) => filled($value));

            $response = $this->client()
                ->asForm()
                ->post($this->endpoint('track/waybill') . '?' . $query, $payload);

            if (! $response->successful()) {
                Log::warning('RajaOngkir trackWaybill failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return $this->trackingResult(false, $response);
            }

            return $this->trackingResult(true, $response);
        } catch (\Throwable $e) {
            Log::error('RajaOngkir trackWaybill: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menghubungi RajaOngkir.',
                'data' => null,
                'raw' => null,
            ];
        }
    }

    /**
     * Legacy helpers kept for older API consumers. In V2, destination search is preferred.
     */
    public function getProvinces(): array
    {
        return Province::orderBy('name')->get()->map(fn (Province $province) => [
            'province_id' => $province->id,
            'province' => $province->name,
        ])->toArray();
    }

    public function getCities(?int $provinceId = null): array
    {
        $query = City::with('province')->orderBy('name');

        if ($provinceId) {
            $query->where('province_id', $provinceId);
        }

        return $query->get()->map(fn (City $city) => [
            'city_id' => $city->id,
            'province_id' => $city->province_id,
            'province' => $city->province?->name,
            'type' => $city->type,
            'city_name' => $city->name,
            'postal_code' => $city->postal_code,
        ])->toArray();
    }

    public function syncToDatabase(): array
    {
        Cache::forget('rajaongkir_destination_' . md5('jakarta_15_0'));

        return [
            'provinces' => Province::count(),
            'cities' => City::count(),
            'destinations' => ShippingDestination::count(),
        ];
    }

    private function client(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders(['key' => $this->apiKey])
            ->acceptJson()
            ->timeout(15)
            ->retry(2, 300, null, false);
    }

    private function endpoint(string $path): string
    {
        return $this->baseUrl . '/' . ltrim($path, '/');
    }

    private function normalizeDestination(array $item): array
    {
        return [
            'id' => (int) Arr::get($item, 'id'),
            'label' => (string) Arr::get($item, 'label'),
            'province_name' => Arr::get($item, 'province_name'),
            'city_name' => Arr::get($item, 'city_name'),
            'district_name' => Arr::get($item, 'district_name'),
            'subdistrict_name' => Arr::get($item, 'subdistrict_name'),
            'zip_code' => Arr::get($item, 'zip_code'),
        ];
    }

    private function normalizeRate(array $item): array
    {
        return [
            'name' => (string) Arr::get($item, 'name', Arr::get($item, 'courier_name', '')),
            'code' => Str::lower((string) Arr::get($item, 'code', Arr::get($item, 'courier_code', ''))),
            'service' => (string) Arr::get($item, 'service', Arr::get($item, 'service_code', '')),
            'description' => Arr::get($item, 'description'),
            'cost' => (int) Arr::get($item, 'cost', 0),
            'etd' => Arr::get($item, 'etd'),
            'raw' => $item,
            'is_fallback' => false,
        ];
    }

    private function rememberDestinations(array $destinations): void
    {
        foreach ($destinations as $destination) {
            ShippingDestination::updateOrCreate(
                ['id' => $destination['id']],
                [
                    'label' => $destination['label'],
                    'province_name' => $destination['province_name'],
                    'city_name' => $destination['city_name'],
                    'district_name' => $destination['district_name'],
                    'subdistrict_name' => $destination['subdistrict_name'],
                    'zip_code' => $destination['zip_code'],
                    'last_seen_at' => now(),
                ]
            );
        }
    }

    private function fallbackDestinationSearch(string $search, int $limit): array
    {
        $needle = '%' . Str::lower($search) . '%';

        return ShippingDestination::query()
            ->whereRaw('LOWER(label) LIKE ?', [$needle])
            ->orWhereRaw('LOWER(city_name) LIKE ?', [$needle])
            ->orWhereRaw('LOWER(district_name) LIKE ?', [$needle])
            ->orWhereRaw('LOWER(subdistrict_name) LIKE ?', [$needle])
            ->limit($limit)
            ->get()
            ->map(fn (ShippingDestination $destination) => [
                'id' => $destination->id,
                'label' => $destination->label,
                'province_name' => $destination->province_name,
                'city_name' => $destination->city_name,
                'district_name' => $destination->district_name,
                'subdistrict_name' => $destination->subdistrict_name,
                'zip_code' => $destination->zip_code,
            ])
            ->values()
            ->all();
    }

    private function fallbackRates(int $origin, int $destination, int $weight, string $couriers): array
    {
        $codes = collect(explode(':', $couriers))
            ->map(fn (string $code) => Str::lower(trim($code)))
            ->filter()
            ->unique()
            ->take(5)
            ->values();

        $base = max(25000, (int) ceil($weight / 1000) * 2200);

        return $codes->map(function (string $code, int $index) use ($base) {
            $names = $this->couriers();
            $multiplier = 1 + ($index * 0.08);
            $service = $index === 0 ? 'REG' : ($index === 1 ? 'CARGO' : 'STANDARD');

            return [
                'name' => $names[$code] ?? Str::upper($code),
                'code' => $code,
                'service' => $service,
                'description' => 'Estimasi lokal saat API key belum aktif',
                'cost' => (int) ceil(($base * $multiplier) / 5000) * 5000,
                'etd' => ($index + 2) . '-' . ($index + 5) . ' hari',
                'raw' => null,
                'is_fallback' => true,
            ];
        })->all();
    }

    private function trackingResult(bool $successful, Response $response): array
    {
        $body = $response->json();
        $data = Arr::get($body, 'data');
        $meta = Arr::get($body, 'meta', []);

        return [
            'success' => $successful && filled($data),
            'message' => Arr::get($meta, 'message', $successful ? 'Tracking berhasil diperbarui.' : 'Tracking belum ditemukan.'),
            'data' => $data,
            'raw' => $body,
        ];
    }
}
