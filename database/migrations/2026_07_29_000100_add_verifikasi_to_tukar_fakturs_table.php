<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tukar_fakturs', function (Blueprint $table) {
            $table->timestamp('verified_at')->nullable()->after('status');
            $table->foreignId('verified_by')->nullable()->after('verified_at')
                ->constrained('users')->nullOnDelete();
            $table->string('verified_note')->nullable()->after('verified_by');

            // Daftar verifikasi selalu menyaring berdasarkan status.
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('tukar_fakturs', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropConstrainedForeignId('verified_by');
            $table->dropColumn(['verified_at', 'verified_note']);
        });
    }
};
