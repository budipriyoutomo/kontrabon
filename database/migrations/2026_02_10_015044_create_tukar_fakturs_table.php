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
        Schema::create('tukar_fakturs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('pt_tujuan');
            $table->string('perusahaan_pengaju');
            $table->date('tanggal_tukar');
            $table->string('no_kwitansi');
            $table->decimal('jumlah_rupiah', 15, 2);
            $table->string('nama_pic');
            $table->string('email_penerima');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tukar_fakturs');
    }
};
