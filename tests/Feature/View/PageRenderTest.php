<?php

declare(strict_types=1);

namespace Tests\Feature\View;

use App\Enums\UserRole;
use App\Models\Perusahaan;
use App\Models\TukarFaktur;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Uji asap seluruh halaman: memastikan tiap view benar-benar bisa dirender
 * setelah dipindah ke pustaka komponen, termasuk saat datanya kosong.
 *
 * Yang diperiksa hanya bahwa halaman merespons 200 dan memuat penanda khas
 * tampilan baru — detail perilakunya sudah ditutup test fitur masing-masing.
 */
class PageRenderTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->role(UserRole::Admin)->create();
    }

    private function tukarFaktur(): TukarFaktur
    {
        $perusahaan = Perusahaan::create([
            'nama' => 'PT Vendor Render',
            'kode' => 'VND-RENDER',
            'top' => 30,
            'is_active' => true,
        ]);

        return TukarFaktur::create([
            'pt_tujuan' => 'PT Maharasa Jaya Abadi (Pepper Lunch)',
            'perusahaan_id' => $perusahaan->id,
            'perusahaan_pengaju' => $perusahaan->nama,
            'tanggal_tukar' => now()->toDateString(),
            'no_kwitansi' => 'KW-RENDER-1',
            'jumlah_rupiah' => 1_500_000,
            'nama_pic' => 'PIC Render',
            'email_penerima' => 'pic@vendor.test',
            'status' => 'pending',
        ]);
    }

    /** @return list<array{0: string}> */
    public static function halamanAdmin(): array
    {
        return [
            'dashboard' => ['/dashboard'],
            'tukar faktur' => ['/admin/tukar-faktur'],
            'verifikasi' => ['/admin/verifikasi'],
            'billing' => ['/billing'],
            'rekap billing' => ['/billing/rekap'],
            'master perusahaan' => ['/admin/perusahaan'],
            'tambah perusahaan' => ['/admin/perusahaan/create'],
            'manajemen pengguna' => ['/admin/users'],
            'tambah pengguna' => ['/admin/users/create'],
            'profil' => ['/profile'],
        ];
    }

    /**
     * Tag komponen yang gagal dikompilasi ikut tercetak apa adanya ke HTML,
     * sehingga kontrolnya hilang dari halaman tanpa memicu error apa pun —
     * halaman tetap 200. Penyebab tersering: direktif Blade selain @class dan
     * @style dipakai di dalam tag <x-...>, misalnya @checked atau @unless.
     */
    private function assertSemuaKomponenTerkompilasi(string $html, string $url): void
    {
        $this->assertDoesNotMatchRegularExpression(
            '/<x-[\w.-]+/',
            $html,
            "Halaman {$url} memuat tag komponen Blade yang tidak terkompilasi."
        );
    }

    /**
     * @dataProvider halamanAdmin
     */
    public function test_halaman_admin_terender_saat_data_kosong(string $url): void
    {
        $response = $this->actingAs($this->admin())->get($url);

        $response->assertOk();
        $response->assertSee('bg-sidebar', false);

        $this->assertSemuaKomponenTerkompilasi($response->getContent(), $url);
    }

    /**
     * @dataProvider halamanAdmin
     */
    public function test_halaman_admin_terender_saat_ada_data(string $url): void
    {
        $this->tukarFaktur();

        $response = $this->actingAs($this->admin())->get($url);

        $response->assertOk();

        $this->assertSemuaKomponenTerkompilasi($response->getContent(), $url);
    }

    public function test_halaman_detail_tukar_faktur_dan_perusahaan(): void
    {
        $tukarFaktur = $this->tukarFaktur();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get("/admin/tukar-faktur/{$tukarFaktur->id}")
            ->assertOk()
            ->assertSee($tukarFaktur->no_kwitansi);

        $this->actingAs($admin)
            ->get("/admin/perusahaan/{$tukarFaktur->perusahaan_id}")
            ->assertOk()
            ->assertSee('Riwayat Tukar Faktur');

        $response = $this->actingAs($admin)
            ->get("/admin/perusahaan/{$tukarFaktur->perusahaan_id}/edit");

        $response->assertOk();

        $this->assertSemuaKomponenTerkompilasi($response->getContent(), 'edit perusahaan');
    }

    public function test_halaman_edit_pengguna_terender(): void
    {
        $admin = $this->admin();
        $target = User::factory()->role(UserRole::Kontrabon)->create();

        foreach ([$target->id, $admin->id] as $id) {
            $response = $this->actingAs($admin)->get("/admin/users/{$id}/edit");

            $response->assertOk();

            $this->assertSemuaKomponenTerkompilasi($response->getContent(), "edit pengguna {$id}");
        }
    }

    public function test_halaman_publik_tukar_faktur_tidak_lagi_memuat_bootstrap(): void
    {
        // Form publik dijaga middleware wednesday.office, jadi waktunya
        // dikunci ke hari Rabu jam kerja agar halamannya bisa dirender.
        $this->travelTo(Carbon::parse('2026-08-05 10:00:00', 'Asia/Jakarta'));

        $response = $this->get('/kontrabon');

        $response->assertOk();
        $response->assertDontSee('bootstrap', false);
        $response->assertSee('Tukar Faktur Online');
    }

    public function test_halaman_login_memakai_kartu_shadcn(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('bg-card', false);
        $response->assertSee('Masuk ke akun');
    }
}
