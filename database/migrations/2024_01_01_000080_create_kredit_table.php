<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kredit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengajuan_kredit')->constrained('pengajuan_kredit')->cascadeOnDelete();
            $table->foreignId('id_metode_bayar')->nullable()->constrained('metode_bayar')->nullOnDelete();
            $table->date('tgl_mulai_kredit');
            $table->date('tgl_selesai_kredit')->nullable();
            $table->double('sisa_kredit')->default(0);
            $table->enum('status_kredit', ['Dicicil', 'Macet', 'Lunas'])->default('Dicicil');
            $table->text('keterangan_status_kredit')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kredit');
    }
};
