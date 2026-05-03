<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pengajuan_kredit', 'id_metode_bayar')) {
            Schema::table('pengajuan_kredit', function (Blueprint $table) {
                $table->foreignId('id_metode_bayar')
                    ->nullable()
                    ->after('id_jenis_cicilan')
                    ->constrained('metode_bayar')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pengajuan_kredit', 'id_metode_bayar')) {
            Schema::table('pengajuan_kredit', function (Blueprint $table) {
                $table->dropConstrainedForeignId('id_metode_bayar');
            });
        }
    }
};
