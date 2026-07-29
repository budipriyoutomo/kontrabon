<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Akun user dari database lama (localhost.sql). Hash password ikut dipindah
 * apa adanya, jadi semua user tetap bisa login memakai password lama.
 *
 * Idempoten: dicocokkan lewat `email`, password yang sudah diganti di database
 * baru TIDAK ditimpa.
 */
class UserLegacySeeder extends Seeder
{
    public function run(): void
    {
        $users = json_decode(
            file_get_contents(database_path('seeders/data/users.json')),
            true
        );

        $baru = 0;

        foreach ($users as $user) {
            $sudahAda = DB::table('users')->where('email', $user['email'])->exists();

            if ($sudahAda) {
                continue;
            }

            DB::table('users')->insert([
                'id'                => $user['id'],
                'name'              => $user['name'],
                'email'             => $user['email'],
                'email_verified_at' => $user['email_verified_at'],
                'password'          => $user['password'],
                'remember_token'    => null,
                'created_at'        => $user['created_at'],
                'updated_at'        => $user['updated_at'],
            ]);

            $baru++;
        }

        $this->command->info(sprintf(
            'User: %d ditambahkan, %d dilewati karena email sudah terdaftar.',
            $baru,
            count($users) - $baru
        ));
    }
}
