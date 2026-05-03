<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metode_bayar', function (Blueprint $table) {
            $table->id();
            $table->string('metode_pembayaran', 30)->nullable();
            $table->string('tempat_bayar', 50)->nullable();
            $table->string('no_rekening', 25)->nullable();
            $table->string('url_logo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metode_bayar');
    }
};
