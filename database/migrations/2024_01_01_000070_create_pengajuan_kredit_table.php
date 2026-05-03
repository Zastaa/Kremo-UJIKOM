<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_kredit', function (Blueprint $table) {
            $table->id();
            $table->date('tgl_pengajuan_kredit');
            $table->foreignId('id_pelanggan')->constrained('pelanggan')->cascadeOnDelete();
            $table->foreignId('id_motor')->constrained('motor')->cascadeOnDelete();
            $table->integer('harga_cash');
            $table->integer('dp');
            $table->foreignId('id_jenis_cicilan')->constrained('jenis_cicilan');
            $table->double('harga_kredit');
            $table->foreignId('id_asuransi')->nullable()->constrained('asuransi')->nullOnDelete();
            $table->double('biaya_asuransi_perbulan')->default(0);
            $table->double('cicilan_perbulan')->default(0);
            $table->string('url_kk')->nullable();
            $table->string('url_ktp')->nullable();
            $table->string('url_npwp')->nullable();
            $table->string('url_slip_gaji')->nullable();
            $table->string('url_foto')->nullable();
            $table->enum('status_pengajuan', [
                'Menunggu Konfirmasi', 'Diproses', 'Survey',
                'Disetujui', 'Ditolak',
                'Dibatalkan Pembeli', 'Dibatalkan Penjual',
                'Bermasalah', 'Diterima'
            ])->default('Menunggu Konfirmasi');
            $table->string('keterangan_status_pengajuan')->nullable();
            $table->foreignId('surveyor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan_survey')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_kredit');
    }
};
