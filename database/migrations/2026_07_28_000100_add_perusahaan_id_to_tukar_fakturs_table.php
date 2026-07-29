<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tukar_fakturs', function (Blueprint $table) {
            // Nullable: kolom perusahaan_pengaju tetap dipertahankan sebagai
            // snapshot nama saat pengajuan (dipakai PDF & email).
            $table->foreignUuid('perusahaan_id')
                ->nullable()
                ->after('pt_tujuan')
                ->constrained('perusahaans')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tukar_fakturs', function (Blueprint $table) {
            $table->dropForeign(['perusahaan_id']);
            $table->dropColumn('perusahaan_id');
        });
    }
};
