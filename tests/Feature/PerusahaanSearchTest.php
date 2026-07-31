<?php

namespace Tests\Feature;

use App\Models\Perusahaan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerusahaanSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_kueri_terlalu_pendek_tidak_mengembalikan_apa_apa(): void
    {
        Perusahaan::factory()->create(['nama' => 'PT Vendor Jaya']);

        $this->getJson(route('perusahaan.search', ['q' => 'P']))
            ->assertOk()
            ->assertExactJson(['data' => []]);
    }

    public function test_mencari_supplier_berdasarkan_awalan_nama(): void
    {
        $cocok = Perusahaan::factory()->create(['nama' => 'PT Vendor Jaya', 'top' => 30]);
        Perusahaan::factory()->create(['nama' => 'CV Lain Sekali']);

        $response = $this->getJson(route('perusahaan.search', ['q' => 'PT Vendor']))
            ->assertOk();

        $data = $response->json('data');

        $this->assertCount(1, $data);
        $this->assertSame($cocok->id, $data[0]['id']);
        $this->assertSame('PT Vendor Jaya', $data[0]['nama']);
        $this->assertSame(30, $data[0]['top']);
    }

    public function test_supplier_nonaktif_tidak_muncul(): void
    {
        Perusahaan::factory()->nonaktif()->create(['nama' => 'PT Sudah Berhenti']);

        $this->getJson(route('perusahaan.search', ['q' => 'PT Sudah']))
            ->assertOk()
            ->assertExactJson(['data' => []]);
    }

    /**
     * Endpoint ini terbuka untuk form publik, jadi kontak supplier tidak
     * boleh ikut terkirim ke pengunjung yang belum login.
     */
    public function test_kontak_supplier_tidak_dibocorkan_ke_pengunjung_publik(): void
    {
        Perusahaan::factory()->create([
            'nama' => 'PT Vendor Jaya',
            'nama_pic' => 'Budi Rahasia',
            'email' => 'budi@vendor.test',
        ]);

        $data = $this->getJson(route('perusahaan.search', ['q' => 'PT Vendor']))
            ->assertOk()
            ->json('data');

        $this->assertArrayNotHasKey('nama_pic', $data[0]);
        $this->assertArrayNotHasKey('email', $data[0]);
    }

    public function test_pengguna_login_menerima_kontak_untuk_auto_isi(): void
    {
        Perusahaan::factory()->create([
            'nama' => 'PT Vendor Jaya',
            'nama_pic' => 'Budi Rahasia',
            'email' => 'budi@vendor.test',
        ]);

        $data = $this->actingAs(User::factory()->create())
            ->getJson(route('perusahaan.search', ['q' => 'PT Vendor']))
            ->assertOk()
            ->json('data');

        $this->assertSame('Budi Rahasia', $data[0]['nama_pic']);
        $this->assertSame('budi@vendor.test', $data[0]['email']);
    }

    public function test_hasil_dibatasi_dua_puluh_baris(): void
    {
        Perusahaan::factory()->count(25)->create([
            'nama' => fn () => 'PT Massal ' . fake()->unique()->numerify('####'),
        ]);

        $data = $this->getJson(route('perusahaan.search', ['q' => 'PT Massal']))
            ->assertOk()
            ->json('data');

        $this->assertCount(20, $data);
    }
}
