<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Urutan wajib: master perusahaan lebih dulu, karena
        // TukarFakturLegacySeeder menyambungkan perusahaan_id lewat nama.
        $this->call([
            PerusahaanMasterSeeder::class,
            UserLegacySeeder::class,
            TukarFakturLegacySeeder::class,
        ]);

        // PerusahaanSeeder (versi lama) sengaja tidak dipanggil: master kini
        // bersumber dari file Excel, bukan diturunkan dari data pengajuan.
    }
}
