<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('motor', function (Blueprint $table) {
            $table->unsignedInteger('berat_gram')->nullable()->after('harga_jual');
        });

        DB::table('motor')->whereNull('berat_gram')->update([
            'berat_gram' => (int) config('rajaongkir.default_weight', 125000),
        ]);
    }

    public function down(): void
    {
        Schema::table('motor', function (Blueprint $table) {
            $table->dropColumn('berat_gram');
        });
    }
};
