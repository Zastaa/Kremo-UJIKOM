<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $kredits = DB::table('kredit')
            ->whereNotNull('id_metode_bayar')
            ->get(['id_pengajuan_kredit', 'id_metode_bayar']);

        foreach ($kredits as $kredit) {
            DB::table('pengajuan_kredit')
                ->where('id', $kredit->id_pengajuan_kredit)
                ->whereNull('id_metode_bayar')
                ->update(['id_metode_bayar' => $kredit->id_metode_bayar]);
        }

        $usersByEmail = DB::table('users')
            ->whereNotNull('email')
            ->pluck('id', 'email');

        if ($usersByEmail->isEmpty()) {
            return;
        }

        DB::table('pelanggan')
            ->whereNull('created_by')
            ->whereNotNull('email')
            ->orderBy('id')
            ->chunkById(100, function ($pelanggan) use ($usersByEmail) {
                foreach ($pelanggan as $item) {
                    $userId = $usersByEmail->get($item->email);

                    if ($userId) {
                        DB::table('pelanggan')
                            ->where('id', $item->id)
                            ->update(['created_by' => $userId]);
                    }
                }
            });

        DB::table('pengajuan_kredit')
            ->join('pelanggan', 'pengajuan_kredit.id_pelanggan', '=', 'pelanggan.id')
            ->whereNull('pengajuan_kredit.created_by')
            ->whereNotNull('pelanggan.email')
            ->select('pengajuan_kredit.id', 'pelanggan.email')
            ->orderBy('pengajuan_kredit.id')
            ->chunkById(100, function ($pengajuan) use ($usersByEmail) {
                foreach ($pengajuan as $item) {
                    $userId = $usersByEmail->get($item->email);

                    if ($userId) {
                        DB::table('pengajuan_kredit')
                            ->where('id', $item->id)
                            ->update(['created_by' => $userId]);
                    }
                }
            }, 'pengajuan_kredit.id', 'id');
    }

    public function down(): void
    {
        //
    }
};
