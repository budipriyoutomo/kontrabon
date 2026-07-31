<?php

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default(UserRole::Verifikator->value)->after('password')->index();
            $table->boolean('is_active')->default(true)->after('role');
        });

        // Semua akun yang sudah ada dijadikan admin. Tanpa ini, begitu
        // middleware role aktif, seluruh user lama langsung terkunci dari
        // halaman /admin dan tidak ada yang bisa membuat akun baru.
        DB::table('users')->update([
            'role' => UserRole::Admin->value,
            'is_active' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn(['role', 'is_active']);
        });
    }
};
