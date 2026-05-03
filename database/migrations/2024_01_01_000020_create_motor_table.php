<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motor', function (Blueprint $table) {
            $table->id();
            $table->string('nama_motor', 100);
            $table->foreignId('id_jenis')->constrained('jenis_motor')->cascadeOnDelete();
            $table->integer('harga_jual');
            $table->text('deskripsi_motor')->nullable();
            $table->string('warna', 50)->nullable();
            $table->string('kapasitas_mesin', 10)->nullable();
            $table->year('tahun_produksi')->nullable();
            $table->string('foto1')->nullable();
            $table->string('foto2')->nullable();
            $table->string('foto3')->nullable();
            $table->integer('stok')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('motor');
    }
};
