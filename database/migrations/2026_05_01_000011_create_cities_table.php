<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary(); // ID dari RajaOngkir
            $table->unsignedBigInteger('province_id');
            $table->string('name');
            $table->string('type'); // Kabupaten / Kota
            $table->string('postal_code')->nullable();

            $table->foreign('province_id')->references('id')->on('provinces')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
