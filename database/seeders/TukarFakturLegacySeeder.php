<?php

namespace Database\Seeders;

use App\Models\Perusahaan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Riwayat tukar faktur dari database lama (localhost.sql), periode
 * 2026-02-04 s/d 2026-07-22.
 *
 * Data sudah diproses lebih dulu:
 *  - `perusahaan_pengaju` diseragamkan ke nama resmi di master perusahaan
 *    (database lama punya 156 varian ejaan untuk ~123 vendor).
 *  - Baris yang bertabrakan dengan unique index tukar_fakturs_pengajuan_unique
 *    disaring, disimpan satu baris per (pt_tujuan, perusahaan_pengaju,
 *    tanggal_tukar) yaitu yang `created_at`-nya paling baru. 154 dari 1349
 *    baris dump tidak ikut; daftarnya ada di database/seeders/data/dibuang.csv.
 *  - `no_kwitansi` di-uppercase + trim mengikuti mutator TukarFaktur.
 *
 * Idempoten: memakai upsert dengan `id` asli dari dump.
 */
class TukarFakturLegacySeeder extends Seeder
{
    public function run(): void
    {
        $rows = json_decode(
            file_get_contents(database_path('seeders/data/tukar_fakturs.json')),
            true
        );

        // Nama sudah diseragamkan saat generate, jadi pemetaan ini pasti kena.
        $perusahaanId = Perusahaan::withTrashed()->pluck('id', 'nama');

        $tanpaMaster = [];

        $payload = array_map(function (array $row) use ($perusahaanId, &$tanpaMaster) {
            $id = $perusahaanId[$row['perusahaan_pengaju']] ?? null;

            if ($id === null) {
                $tanpaMaster[$row['perusahaan_pengaju']] = true;
            }

            return $row + ['perusahaan_id' => $id];
        }, $rows);

        if ($tanpaMaster !== []) {
            $this->command->error(
                'Jalankan PerusahaanMasterSeeder lebih dulu. Nama tanpa master: '
                . implode(', ', array_keys($tanpaMaster))
            );

            return;
        }

        $kolom = [
            'pt_tujuan', 'perusahaan_id', 'perusahaan_pengaju', 'tanggal_tukar',
            'no_kwitansi', 'jumlah_rupiah', 'nama_pic', 'email_penerima',
            'tanggal_pembayaran', 'status', 'created_at', 'updated_at',
        ];

        foreach (array_chunk($payload, 200) as $chunk) {
            DB::table('tukar_fakturs')->upsert($chunk, ['id'], $kolom);
        }

        $this->command->info(sprintf(
            'Riwayat tukar faktur: %d baris ter-seed, total di tabel sekarang %d.',
            count($payload),
            DB::table('tukar_fakturs')->count()
        ));
    }
}
