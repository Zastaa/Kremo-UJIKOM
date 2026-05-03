<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengiriman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengajuan_kredit')->constrained('pengajuan_kredit')->cascadeOnDelete();
            $table->string('no_invoice')->nullable();
            $table->datetime('tgl_kirim')->nullable();
            $table->datetime('tgl_tiba')->nullable();
            $table->enum('status_kirim', ['Sedang Dikirim', 'Tiba Di Tujuan'])->default('Sedang Dikirim');
            $table->string('nama_kurir', 30)->nullable();
            $table->string('telpon_kurir', 15)->nullable();
            $table->string('bukti_foto')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengiriman');
    }
};
