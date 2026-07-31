<?php

namespace Tests\Feature;

use App\Models\Perusahaan;
use App\Models\TukarFaktur;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Form publik /kontrabon.
 *
 * Nama supplier diketik manual dan harus cocok PERSIS dengan master —
 * termasuk huruf besar/kecil. Kecocokan sebagian huruf tidak diterima,
 * karena nama inilah yang tersimpan sebagai identitas pengaju.
 */
class PengajuanTukarFakturTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Form hanya dibuka hari Rabu jam kerja (OnlyWednesdayOfficeHour).
        $this->travelTo(Carbon::parse('2026-08-05 10:00', 'Asia/Jakarta'));
    }

    private function payload(array $ganti = []): array
    {
        return array_merge([
            'pt_tujuan' => 'PT Maharasa Jaya Abadi (Pepper Lunch)',
            'perusahaan_pengaju' => 'PT Vendor Jaya',
            'tanggal_tukar' => '2026-08-05',
            'no_kwitansi' => 'kw-000123',
            'jumlah_rupiah' => 2_500_000,
            'nama_pic' => 'Budi',
            'email_penerima' => 'budi@vendor.test',
        ], $ganti);
    }

    public function test_form_bisa_dibuka_pada_hari_rabu(): void
    {
        $this->get('/kontrabon')->assertOk();
    }

    public function test_nama_yang_ditulis_sama_persis_diterima_dan_relasinya_tersambung(): void
    {
        $perusahaan = Perusahaan::factory()->create(['nama' => 'PT Vendor Jaya']);

        $this->post('/kontrabon', $this->payload())
            ->assertRedirect('/kontrabon/success');

        $data = TukarFaktur::firstOrFail();

        $this->assertSame('PT Vendor Jaya', $data->perusahaan_pengaju);
        // Nama cocok persis -> relasi ke master ikut tersambung.
        $this->assertSame($perusahaan->id, $data->perusahaan_id);
        // Kwitansi tetap dinormalkan jadi huruf kapital.
        $this->assertSame('KW-000123', $data->no_kwitansi);
    }

    public function test_spasi_di_ujung_nama_dirapikan_bukan_ditolak(): void
    {
        Perusahaan::factory()->create(['nama' => 'PT Vendor Jaya']);

        $this->post('/kontrabon', $this->payload([
            'perusahaan_pengaju' => '  PT Vendor Jaya  ',
        ]))->assertRedirect('/kontrabon/success');

        $this->assertSame('PT Vendor Jaya', TukarFaktur::firstOrFail()->perusahaan_pengaju);
    }

    public function test_beda_huruf_besar_kecil_ditolak_tapi_disarankan_penulisannya(): void
    {
        Perusahaan::factory()->create(['nama' => 'PT Vendor Jaya']);

        $this->post('/kontrabon', $this->payload([
            'perusahaan_pengaju' => 'pt vendor jaya',
        ]))
            ->assertSessionHasErrors('perusahaan_pengaju')
            ->assertSessionHas('saran_perusahaan', 'PT Vendor Jaya');

        $this->assertDatabaseCount('tukar_fakturs', 0);
    }

    public function test_saran_penulisan_muncul_di_form(): void
    {
        Perusahaan::factory()->create(['nama' => 'PT Vendor Jaya']);

        // back() butuh referer; tanpa ini redirectnya mendarat di "/".
        $this->from('/kontrabon')
            ->followingRedirects()
            ->post('/kontrabon', $this->payload([
                'perusahaan_pengaju' => 'pt vendor jaya',
            ]))
            ->assertOk()
            ->assertSee('Pakai penulisan ini')
            ->assertSee('data-saran="PT Vendor Jaya"', false);
    }

    /** Inti aturannya: sebagian huruf tidak boleh dianggap cocok. */
    public function test_nama_yang_hanya_sebagian_ditolak(): void
    {
        Perusahaan::factory()->create(['nama' => 'PT Vendor Jaya Sentosa']);

        $this->post('/kontrabon', $this->payload([
            'perusahaan_pengaju' => 'PT Vendor Jaya',
        ]))->assertSessionHasErrors('perusahaan_pengaju');

        $this->assertDatabaseCount('tukar_fakturs', 0);
    }

    /** Nama asing tidak boleh dapat saran — tidak ada penulisan yang "mirip". */
    public function test_nama_yang_tidak_terdaftar_ditolak_tanpa_saran(): void
    {
        Perusahaan::factory()->create(['nama' => 'PT Vendor Jaya']);

        $this->post('/kontrabon', $this->payload([
            'perusahaan_pengaju' => 'PT Tidak Pernah Ada',
        ]))
            ->assertSessionHasErrors('perusahaan_pengaju')
            ->assertSessionMissing('saran_perusahaan');

        $this->assertDatabaseCount('tukar_fakturs', 0);
    }

    public function test_supplier_nonaktif_ditolak(): void
    {
        Perusahaan::factory()->nonaktif()->create(['nama' => 'PT Sudah Berhenti']);

        $this->post('/kontrabon', $this->payload([
            'perusahaan_pengaju' => 'PT Sudah Berhenti',
        ]))->assertSessionHasErrors('perusahaan_pengaju');

        $this->assertDatabaseCount('tukar_fakturs', 0);
    }

    public function test_satu_supplier_hanya_boleh_satu_pengajuan_per_pt_per_tanggal(): void
    {
        Perusahaan::factory()->create(['nama' => 'PT Vendor Jaya']);

        $this->post('/kontrabon', $this->payload())
            ->assertRedirect('/kontrabon/success');

        $this->post('/kontrabon', $this->payload([
            'no_kwitansi' => 'KW-000999',
        ]))->assertSessionHasErrors('perusahaan_pengaju');

        $this->assertDatabaseCount('tukar_fakturs', 1);
    }

    public function test_form_ditolak_di_luar_hari_rabu(): void
    {
        $this->travelTo(Carbon::parse('2026-08-06 10:00', 'Asia/Jakarta'));

        $this->get('/kontrabon')->assertForbidden();
    }
}
