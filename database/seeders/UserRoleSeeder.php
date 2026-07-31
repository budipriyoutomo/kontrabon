<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Satu akun contoh untuk tiap peran, dipakai saat pengujian manual.
 *
 * Idempoten: dicocokkan lewat `email`. Password akun yang sudah ada TIDAK
 * ditimpa, jadi aman dijalankan ulang di server yang sudah dipakai.
 */
class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        $akun = [
            ['name' => 'Administrator', 'email' => 'admin@maharasa.test', 'role' => UserRole::Admin],
            ['name' => 'Staff Kontrabon', 'email' => 'kontrabon@maharasa.test', 'role' => UserRole::Kontrabon],
            ['name' => 'Staff Verifikator', 'email' => 'verifikator@maharasa.test', 'role' => UserRole::Verifikator],
            ['name' => 'Staff Billing', 'email' => 'billing@maharasa.test', 'role' => UserRole::Billing],
        ];

        $baru = 0;

        foreach ($akun as $data) {
            if (User::where('email', $data['email'])->exists()) {
                continue;
            }

            User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role'],
                'is_active' => true,
                'password' => 'password',
                'email_verified_at' => now(),
            ]);

            $baru++;
        }

        $this->command->info(sprintf(
            'Akun peran: %d ditambahkan, %d dilewati karena email sudah terdaftar.',
            $baru,
            count($akun) - $baru
        ));

        if ($baru > 0) {
            $this->command->warn('Password awal seluruh akun contoh: "password". Ganti sebelum dipakai di produksi.');
        }
    }
}
