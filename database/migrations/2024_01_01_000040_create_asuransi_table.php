<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asuransi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_perusahaan_asuransi', 30);
            $table->string('nama_asuransi', 50);
            $table->decimal('margin_asuransi', 8, 2); // percentage
            $table->string('no_rekening')->nullable();
            $table->string('url_logo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asuransi');
    }
};
