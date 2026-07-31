<?php

namespace Tests\Feature\Admin;

use App\Enums\TukarFakturStatus;
use App\Enums\UserRole;
use App\Models\TukarFaktur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifikasiTest extends TestCase
{
    use RefreshDatabase;

    private function verifikator(): User
    {
        return User::factory()->role(UserRole::Verifikator)->create();
    }

    public function test_hanya_verifikator_dan_admin_yang_bisa_membuka_halaman_verifikasi(): void
    {
        foreach ([UserRole::Verifikator, UserRole::Admin] as $role) {
            $this->actingAs(User::factory()->role($role)->create())
                ->get(route('admin.verifikasi.index'))
                ->assertOk();
        }

        foreach ([UserRole::Kontrabon, UserRole::Billing] as $role) {
            $this->actingAs(User::factory()->role($role)->create())
                ->get(route('admin.verifikasi.index'))
                ->assertForbidden();
        }
    }

    public function test_daftar_verifikasi_hanya_menampilkan_data_yang_emailnya_sudah_terkirim(): void
    {
        $menunggu = TukarFaktur::factory()->emailSent()->create();
        $belumKirim = TukarFaktur::factory()->create(); // pending
        $sudah = TukarFaktur::factory()->verified()->create();

        $this->actingAs($this->verifikator())
            ->get(route('admin.verifikasi.index'))
            ->assertOk()
            ->assertSee($menunggu->no_kwitansi)
            ->assertDontSee($belumKirim->no_kwitansi)
            ->assertDontSee($sudah->no_kwitansi);
    }

    public function test_verifikator_bisa_memverifikasi_data_yang_emailnya_sudah_terkirim(): void
    {
        $verifikator = $this->verifikator();
        $data = TukarFaktur::factory()->emailSent()->create();

        $this->actingAs($verifikator)
            ->post(route('admin.verifikasi.verify', $data->id), [
                'verified_note' => 'Dokumen lengkap',
            ])
            ->assertRedirect();

        $data->refresh();

        $this->assertSame(TukarFakturStatus::Verified, $data->status);
        $this->assertSame($verifikator->id, $data->verified_by);
        $this->assertSame('Dokumen lengkap', $data->verified_note);
        $this->assertNotNull($data->verified_at);
    }

    public function test_data_pending_tidak_bisa_langsung_diverifikasi(): void
    {
        $data = TukarFaktur::factory()->create(); // pending

        $this->actingAs($this->verifikator())
            ->post(route('admin.verifikasi.verify', $data->id))
            ->assertRedirect()
            ->assertSessionHas('error');

        $data->refresh();

        $this->assertSame(TukarFakturStatus::Pending, $data->status);
        $this->assertNull($data->verified_at);
    }

    public function test_data_tidak_bisa_diverifikasi_dua_kali(): void
    {
        $verifikatorPertama = $this->verifikator();
        $data = TukarFaktur::factory()->verified($verifikatorPertama)->create();
        $waktuAwal = $data->verified_at;

        $this->actingAs($this->verifikator())
            ->post(route('admin.verifikasi.verify', $data->id))
            ->assertSessionHas('error');

        $data->refresh();

        $this->assertSame($verifikatorPertama->id, $data->verified_by);
        $this->assertEquals($waktuAwal->timestamp, $data->verified_at->timestamp);
    }

    public function test_kontrabon_tidak_bisa_memverifikasi(): void
    {
        $data = TukarFaktur::factory()->emailSent()->create();

        $this->actingAs(User::factory()->role(UserRole::Kontrabon)->create())
            ->post(route('admin.verifikasi.verify', $data->id))
            ->assertForbidden();

        $this->assertSame(TukarFakturStatus::EmailSent, $data->fresh()->status);
    }

    public function test_verifikasi_massal_hanya_memproses_yang_menunggu(): void
    {
        $verifikator = $this->verifikator();

        $menunggu = TukarFaktur::factory()->emailSent()->count(3)->create();
        $pending = TukarFaktur::factory()->create();

        $ids = $menunggu->pluck('id')->push($pending->id)->all();

        $this->actingAs($verifikator)
            ->post(route('admin.verifikasi.bulk'), ['ids' => $ids])
            ->assertRedirect()
            ->assertSessionHas('success');

        foreach ($menunggu as $item) {
            $this->assertSame(TukarFakturStatus::Verified, $item->fresh()->status);
        }

        // Yang belum terkirim emailnya tidak ikut terverifikasi.
        $this->assertSame(TukarFakturStatus::Pending, $pending->fresh()->status);
    }

    public function test_data_terverifikasi_tidak_bisa_diubah_atau_dihapus(): void
    {
        $kontrabon = User::factory()->role(UserRole::Kontrabon)->create();
        $data = TukarFaktur::factory()->verified()->create();

        $this->actingAs($kontrabon)
            ->put(route('admin.tukar-faktur.update', $data->id), [
                'jumlah_rupiah' => 999,
                'pt_tujuan' => 'PT Lain',
                'perusahaan_pengaju' => 'PT Lain',
                'tanggal_tukar' => now()->toDateString(),
                'no_kwitansi' => 'KW-BARU',
                'nama_pic' => 'PIC Baru',
                'email_penerima' => 'baru@vendor.test',
            ])
            ->assertSessionHas('error');

        $this->assertNotEquals(999, $data->fresh()->jumlah_rupiah);

        $this->actingAs($kontrabon)
            ->delete(route('admin.tukar-faktur.destroy', $data->id))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('tukar_fakturs', ['id' => $data->id]);
    }

    public function test_tanggal_bayar_tidak_bisa_diisi_ulang_setelah_email_terkirim(): void
    {
        $data = TukarFaktur::factory()->emailSent()->create();
        $tanggalAwal = $data->tanggal_pembayaran;

        $this->actingAs(User::factory()->role(UserRole::Kontrabon)->create())
            ->post(route('admin.tukar-faktur.payment-date', $data->id), [
                'tanggal_pembayaran' => now()->addDays(99)->toDateString(),
            ])
            ->assertSessionHas('info');

        $this->assertEquals($tanggalAwal, $data->fresh()->tanggal_pembayaran);
    }
}
