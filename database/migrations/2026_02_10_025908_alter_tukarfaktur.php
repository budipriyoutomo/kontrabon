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
        //
        Schema::table('tukar_fakturs', function (Blueprint $table) {
            $table->date('tanggal_pembayaran')->nullable()->after('email_penerima');
            $table->string('status')->default('pending')->after('tanggal_pembayaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('tukar_fakturs', function (Blueprint $table) {
            $table->dropColumn('tanggal_pembayaran');
            $table->dropColumn('status');
        });
    }
};
