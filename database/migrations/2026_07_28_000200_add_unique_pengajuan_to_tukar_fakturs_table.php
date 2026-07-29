<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rapikan data lama supaya konsisten dengan mutator model.
        DB::table('tukar_fakturs')->update([
            'no_kwitansi' => DB::raw('UPPER(TRIM(no_kwitansi))'),
        ]);

        // Jaring pengaman di level database: satu perusahaan hanya boleh
        // punya satu pengajuan per PT tujuan pada tanggal tukar yang sama.
        Schema::table('tukar_fakturs', function (Blueprint $table) {
            $table->unique(
                ['pt_tujuan', 'perusahaan_pengaju', 'tanggal_tukar'],
                'tukar_fakturs_pengajuan_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tukar_fakturs', function (Blueprint $table) {
            $table->dropUnique('tukar_fakturs_pengajuan_unique');
        });
    }
};
