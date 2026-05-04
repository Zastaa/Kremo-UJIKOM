<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const SAMPLE_WEIGHTS = [
        'Honda Supra X 125' => 105000,
        'Yamaha NMAX 155' => 132000,
        'Kawasaki Ninja ZX-25R' => 182000,
        'Honda CRF250 Rally' => 152000,
        'Honda Beat' => 90000,
        'Yamaha Aerox 155' => 125000,
    ];

    public function up(): void
    {
        $defaultWeight = (int) config('rajaongkir.default_weight', 125000);

        foreach (self::SAMPLE_WEIGHTS as $name => $weight) {
            DB::table('motor')
                ->where('nama_motor', $name)
                ->where(function ($query) use ($defaultWeight) {
                    $query->whereNull('berat_gram')
                        ->orWhere('berat_gram', $defaultWeight);
                })
                ->update(['berat_gram' => $weight]);
        }
    }

    public function down(): void
    {
        $defaultWeight = (int) config('rajaongkir.default_weight', 125000);

        foreach (self::SAMPLE_WEIGHTS as $name => $weight) {
            DB::table('motor')
                ->where('nama_motor', $name)
                ->where('berat_gram', $weight)
                ->update(['berat_gram' => $defaultWeight]);
        }
    }
};
