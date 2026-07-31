<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tukar_fakturs', function (Blueprint $table) {
            $table->timestamp('billed_at')->nullable()->after('verified_note');
            $table->foreignId('billed_by')->nullable()->after('billed_at')
                ->constrained('users')->nullOnDelete();

            // Modul billing selalu menyaring status + rentang tanggal bayar,
            // dan merekap per PT tujuan. Tanpa index ini keduanya full scan.
            $table->index(['status', 'tanggal_pembayaran'], 'tukar_fakturs_billing_index');
            $table->index(['pt_tujuan', 'tanggal_pembayaran'], 'tukar_fakturs_pt_bayar_index');
        });
    }

    public function down(): void
    {
        Schema::table('tukar_fakturs', function (Blueprint $table) {
            $table->dropIndex('tukar_fakturs_billing_index');
            $table->dropIndex('tukar_fakturs_pt_bayar_index');
            $table->dropConstrainedForeignId('billed_by');
            $table->dropColumn('billed_at');
        });
    }
};
