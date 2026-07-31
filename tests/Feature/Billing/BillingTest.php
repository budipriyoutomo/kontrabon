<?php

namespace Tests\Feature\Billing;

use App\Enums\TukarFakturStatus;
use App\Enums\UserRole;
use App\Models\TukarFaktur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    private function billing(): User
    {
        return User::factory()->role(UserRole::Billing)->create();
    }

    public function test_hanya_billing_dan_admin_yang_bisa_membuka_modul_billing(): void
    {
        foreach ([UserRole::Billing, UserRole::Admin] as $role) {
            $this->actingAs(User::factory()->role($role)->create())
                ->get(route('billing.index'))
                ->assertOk();
        }

        foreach ([UserRole::Kontrabon, UserRole::Verifikator] as $role) {
            $this->actingAs(User::factory()->role($role)->create())
                ->get(route('billing.index'))
                ->assertForbidden();
        }
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('billing.index'))->assertRedirect(route('login'));
    }

    /**
     * Inti modul ini: angka yang belum diperiksa siapa pun tidak boleh
     * ikut terhitung sebagai tagihan.
     */
    public function test_data_belum_terverifikasi_tidak_bocor_ke_billing(): void
    {
        $pending = TukarFaktur::factory()->create();
        $emailSent = TukarFaktur::factory()->emailSent()->create();
        $verified = TukarFaktur::factory()->verified()->create();

        $this->actingAs($this->billing())
            ->get(route('billing.index'))
            ->assertOk()
            ->assertSee($verified->no_kwitansi)
            ->assertDontSee($pending->no_kwitansi)
            ->assertDontSee($emailSent->no_kwitansi);
    }

    public function test_ringkasan_hanya_menjumlahkan_data_terverifikasi(): void
    {
        TukarFaktur::factory()->verified()->create(['jumlah_rupiah' => 1_000_000]);
        TukarFaktur::factory()->verified()->create(['jumlah_rupiah' => 2_500_000]);
        TukarFaktur::factory()->emailSent()->create(['jumlah_rupiah' => 9_999_999]);

        $response = $this->actingAs($this->billing())
            ->get(route('billing.index'))
            ->assertOk();

        $ringkasan = $response->viewData('ringkasan');

        $this->assertSame(2, $ringkasan['jumlahDokumen']);
        $this->assertEquals(3_500_000, $ringkasan['totalRupiah']);
    }

    public function test_filter_rentang_tanggal_bayar(): void
    {
        $didalam = TukarFaktur::factory()->verified()->create([
            'tanggal_pembayaran' => '2026-08-10',
            'jumlah_rupiah' => 1_000_000,
        ]);

        $diluar = TukarFaktur::factory()->verified()->create([
            'tanggal_pembayaran' => '2026-09-20',
            'jumlah_rupiah' => 7_000_000,
        ]);

        $response = $this->actingAs($this->billing())
            ->get(route('billing.index', [
                'start_bayar' => '2026-08-01',
                'end_bayar' => '2026-08-31',
            ]))
            ->assertOk()
            ->assertSee($didalam->no_kwitansi)
            ->assertDontSee($diluar->no_kwitansi);

        $this->assertEquals(1_000_000, $response->viewData('ringkasan')['totalRupiah']);
    }

    public function test_filter_pt_tujuan(): void
    {
        $target = TukarFaktur::factory()->verified()->create([
            'pt_tujuan' => 'PT Maharasa Jaya Abadi (Pepper Lunch)',
        ]);

        $lain = TukarFaktur::factory()->verified()->create([
            'pt_tujuan' => 'PT Loka Abadi Nanjaya (Waruna)',
        ]);

        $this->actingAs($this->billing())
            ->get(route('billing.index', ['pt_tujuan' => 'PT Maharasa Jaya Abadi (Pepper Lunch)']))
            ->assertOk()
            ->assertSee($target->no_kwitansi)
            ->assertDontSee($lain->no_kwitansi);
    }

    public function test_billing_bisa_memproses_data_terverifikasi(): void
    {
        $petugas = $this->billing();
        $data = TukarFaktur::factory()->verified()->create();

        $this->actingAs($petugas)
            ->post(route('billing.proses', $data->id))
            ->assertRedirect();

        $data->refresh();

        $this->assertSame(TukarFakturStatus::Billing, $data->status);
        $this->assertSame($petugas->id, $data->billed_by);
        $this->assertNotNull($data->billed_at);
    }

    public function test_data_belum_terverifikasi_tidak_bisa_diproses_billing(): void
    {
        $data = TukarFaktur::factory()->emailSent()->create();

        $this->actingAs($this->billing())
            ->post(route('billing.proses', $data->id))
            ->assertSessionHas('error');

        $this->assertSame(TukarFakturStatus::EmailSent, $data->fresh()->status);
    }

    public function test_data_tidak_bisa_diproses_billing_dua_kali(): void
    {
        $petugas = $this->billing();
        $data = TukarFaktur::factory()->verified()->create();

        $this->actingAs($petugas)->post(route('billing.proses', $data->id));

        $waktuAwal = $data->fresh()->billed_at;

        $this->actingAs($this->billing())
            ->post(route('billing.proses', $data->id))
            ->assertSessionHas('error');

        $this->assertSame($petugas->id, $data->fresh()->billed_by);
        $this->assertEquals($waktuAwal->timestamp, $data->fresh()->billed_at->timestamp);
    }

    public function test_verifikator_tidak_bisa_memproses_billing(): void
    {
        $data = TukarFaktur::factory()->verified()->create();

        $this->actingAs(User::factory()->role(UserRole::Verifikator)->create())
            ->post(route('billing.proses', $data->id))
            ->assertForbidden();

        $this->assertSame(TukarFakturStatus::Verified, $data->fresh()->status);
    }

    public function test_proses_massal_melewati_yang_belum_terverifikasi(): void
    {
        $siap = TukarFaktur::factory()->verified()->count(3)->create();
        $belum = TukarFaktur::factory()->emailSent()->create();

        $ids = $siap->pluck('id')->push($belum->id)->all();

        $this->actingAs($this->billing())
            ->post(route('billing.proses-massal'), ['ids' => $ids])
            ->assertRedirect()
            ->assertSessionHas('success');

        foreach ($siap as $item) {
            $this->assertSame(TukarFakturStatus::Billing, $item->fresh()->status);
        }

        $this->assertSame(TukarFakturStatus::EmailSent, $belum->fresh()->status);
    }

    public function test_rekap_mengelompokkan_per_pt_tujuan(): void
    {
        TukarFaktur::factory()->verified()->create([
            'pt_tujuan' => 'PT Maharasa Jaya Abadi (Pepper Lunch)',
            'tanggal_pembayaran' => '2026-08-10',
            'jumlah_rupiah' => 1_000_000,
        ]);

        TukarFaktur::factory()->verified()->create([
            'pt_tujuan' => 'PT Maharasa Jaya Abadi (Pepper Lunch)',
            'tanggal_pembayaran' => '2026-08-10',
            'jumlah_rupiah' => 500_000,
        ]);

        $rekap = $this->actingAs($this->billing())
            ->get(route('billing.rekap'))
            ->assertOk()
            ->viewData('rekap');

        $baris = $rekap['PT Maharasa Jaya Abadi (Pepper Lunch)'];

        $this->assertCount(1, $baris); // satu tanggal bayar
        $this->assertSame(2, (int) $baris[0]->jumlah_dokumen);
        $this->assertEquals(1_500_000, $baris[0]->total_rupiah);
    }

    /**
     * Panel raw data di rekap bergantung pada kunci gabungan PT + tanggal
     * bayar. Kalau normalisasi tanggalnya meleset, rincian jadi kosong
     * padahal angka agregatnya benar — jadi kecocokan kunci diuji langsung.
     */
    public function test_rekap_menyertakan_dokumen_mentah_per_baris(): void
    {
        $satu = TukarFaktur::factory()->verified()->create([
            'pt_tujuan' => 'PT Maharasa Jaya Abadi (Pepper Lunch)',
            'tanggal_pembayaran' => '2026-08-10',
            'jumlah_rupiah' => 1_000_000,
        ]);

        $dua = TukarFaktur::factory()->verified()->create([
            'pt_tujuan' => 'PT Maharasa Jaya Abadi (Pepper Lunch)',
            'tanggal_pembayaran' => '2026-08-10',
            'jumlah_rupiah' => 500_000,
        ]);

        // Tanggal bayar lain: tidak boleh ikut masuk ke baris di atas.
        $lain = TukarFaktur::factory()->verified()->create([
            'pt_tujuan' => 'PT Maharasa Jaya Abadi (Pepper Lunch)',
            'tanggal_pembayaran' => '2026-08-17',
            'jumlah_rupiah' => 250_000,
        ]);

        $response = $this->actingAs($this->billing())
            ->get(route('billing.rekap'))
            ->assertOk();

        $baris = $response->viewData('rekap')['PT Maharasa Jaya Abadi (Pepper Lunch)'];
        $dokumen = $response->viewData('dokumen');

        $rincian = $dokumen[$baris[0]->kunci];

        $this->assertCount(2, $rincian);
        $this->assertEqualsCanonicalizing(
            [$satu->id, $dua->id],
            $rincian->pluck('id')->all()
        );

        $this->assertSame([$lain->id], $dokumen[$baris[1]->kunci]->pluck('id')->all());
    }

    public function test_export_csv_hanya_memuat_data_terverifikasi(): void
    {
        $verified = TukarFaktur::factory()->verified()->create();
        $pending = TukarFaktur::factory()->create();

        $response = $this->actingAs($this->billing())
            ->get(route('billing.export.csv'))
            ->assertOk();

        $isi = $response->streamedContent();

        $this->assertStringContainsString($verified->no_kwitansi, $isi);
        $this->assertStringNotContainsString($pending->no_kwitansi, $isi);
    }

    public function test_export_pdf_bisa_diunduh(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('Ekstensi GD tidak terpasang; dompdf tidak bisa membuat PDF.');
        }

        TukarFaktur::factory()->verified()->create();

        $this->actingAs($this->billing())
            ->get(route('billing.export.pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_billing_mendarat_di_modulnya_setelah_login(): void
    {
        $petugas = User::factory()->role(UserRole::Billing)->create();

        $this->post('/login', [
            'email' => $petugas->email,
            'password' => 'password',
        ])->assertRedirect(route('billing.index'));
    }
}
