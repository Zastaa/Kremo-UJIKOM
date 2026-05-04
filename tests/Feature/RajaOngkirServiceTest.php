<?php

namespace Tests\Feature;

use App\Services\RajaOngkirService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RajaOngkirServiceTest extends TestCase
{
    public function test_cost_calculation_returns_no_local_estimate_without_api_key(): void
    {
        config(['rajaongkir.api_key' => '']);

        $service = app(RajaOngkirService::class);

        $this->assertSame([], $service->calculateDomesticCost(1, 2, 1000, 'jne'));
        $this->assertStringContainsString('API key', $service->lastCostError());
    }

    public function test_cost_calculation_returns_no_local_estimate_when_rajaongkir_rejects_request(): void
    {
        config(['rajaongkir.api_key' => 'test-key']);

        Http::fake([
            '*' => Http::response([
                'meta' => [
                    'message' => 'origin not found',
                    'code' => 404,
                    'status' => 'error',
                ],
                'data' => null,
            ], 404),
        ]);

        $service = app(RajaOngkirService::class);

        $this->assertSame([], $service->calculateDomesticCost(1, 2, 1000, 'jne'));
        $this->assertStringContainsString('origin not found', $service->lastCostError());
    }
}
