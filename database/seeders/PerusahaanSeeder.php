<?php

namespace Database\Seeders;

use App\Models\Perusahaan;
use App\Models\TukarFaktur;
use Illuminate\Database\Seeder;

class PerusahaanSeeder extends Seeder
{
    /**
     * Isi master perusahaan dari data pengajuan yang sudah ada,
     * lalu sambungkan kembali tukar_fakturs.perusahaan_id.
     */
    public function run(): void
    {
        $namaList = TukarFaktur::query()
            ->whereNotNull('perusahaan_pengaju')
            ->where('perusahaan_pengaju', '!=', '')
            ->distinct()
            ->pluck('perusahaan_pengaju');

        foreach ($namaList as $nama) {
            $nama = trim($nama);

            $perusahaan = Perusahaan::firstOrCreate(
                ['nama' => $nama],
                ['is_active' => true]
            );

            TukarFaktur::where('perusahaan_pengaju', $nama)
                ->whereNull('perusahaan_id')
                ->update(['perusahaan_id' => $perusahaan->id]);
        }

        $this->command->info('Master perusahaan terisi: ' . $namaList->count() . ' data.');
    }
}
