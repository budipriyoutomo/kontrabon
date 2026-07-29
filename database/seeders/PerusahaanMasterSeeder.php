<?php

namespace Database\Seeders;

use App\Models\Perusahaan;
use Illuminate\Database\Seeder;

/**
 * Master perusahaan dari file
 * "DUE DATE VENDOR TUKAR FAKTUR MAHARASA ONLINE UPDATE JUL26.xlsx" (sheet SUPPLIER),
 * ditambah vendor yang sudah bertransaksi di database lama tapi belum
 * terdaftar di file tersebut (TOP-nya null, perlu diisi manual lewat menu admin).
 *
 * Idempoten: dijalankan berulang tidak menggandakan data, dicocokkan lewat `nama`.
 */
class PerusahaanMasterSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(
            file_get_contents(database_path('seeders/data/perusahaans.json')),
            true
        );

        $baru = 0;
        $update = 0;

        foreach ($data as $row) {
            $perusahaan = Perusahaan::withTrashed()->firstOrNew(['nama' => $row['nama']]);

            $perusahaan->exists ? $update++ : $baru++;

            // TOP dari Excel selalu menang; vendor tanpa TOP dibiarkan null
            // supaya tidak menimpa nilai yang sudah diisi manual.
            if ($row['top'] !== null) {
                $perusahaan->top = $row['top'];
            }

            if (! $perusahaan->exists) {
                $perusahaan->is_active = true;
            }

            $perusahaan->save();
        }

        $this->command->info(sprintf(
            'Master perusahaan: %d baru, %d diperbarui, total %d.',
            $baru,
            $update,
            count($data)
        ));
    }
}
