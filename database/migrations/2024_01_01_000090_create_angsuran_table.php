<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('angsuran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_kredit')->constrained('kredit')->cascadeOnDelete();
            $table->date('tgl_bayar')->nullable();
            $table->integer('angsuran_ke');
            $table->double('total_bayar');
            $table->enum('status', ['Belum Bayar', 'Lunas', 'Terlambat'])->default('Belum Bayar');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('angsuran');
    }
};
