<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pengajuan_kredit', 'created_by')) {
            Schema::table('pengajuan_kredit', function (Blueprint $table) {
                $table->foreignId('created_by')
                    ->nullable()
                    ->after('id_pelanggan')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('pelanggan', 'created_by')) {
            Schema::table('pelanggan', function (Blueprint $table) {
                $table->foreignId('created_by')
                    ->nullable()
                    ->after('id')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pengajuan_kredit', 'created_by')) {
            Schema::table('pengajuan_kredit', function (Blueprint $table) {
                $table->dropConstrainedForeignId('created_by');
            });
        }

        if (Schema::hasColumn('pelanggan', 'created_by')) {
            Schema::table('pelanggan', function (Blueprint $table) {
                $table->dropConstrainedForeignId('created_by');
            });
        }
    }
};
