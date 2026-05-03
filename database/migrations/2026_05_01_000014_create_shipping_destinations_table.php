<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_destinations', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('label');
            $table->string('province_name')->nullable();
            $table->string('city_name')->nullable();
            $table->string('district_name')->nullable();
            $table->string('subdistrict_name')->nullable();
            $table->string('zip_code', 20)->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index('city_name');
            $table->index('district_name');
            $table->index('subdistrict_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_destinations');
    }
};
