<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Perusahaan;
use App\Models\TukarFaktur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private function user(UserRole $role): User
    {
        return User::factory()->role($role)->create();
    }

    private function tukarFaktur(): TukarFaktur
    {
        // Nama perusahaan unik: satu test memanggil helper ini berkali-kali,
        // sedangkan kolom `nama` bersifat unique.
        static $urutan = 0;
        $urutan++;

        $perusahaan = Perusahaan::create([
            'nama' => "PT Vendor Uji {$urutan}",
            'is_active' => true,
        ]);

        return TukarFaktur::create([
            'pt_tujuan' => 'PT Maharasa Jaya Abadi (Pepper Lunch)',
            'perusahaan_id' => $perusahaan->id,
            'perusahaan_pengaju' => $perusahaan->nama,
            'tanggal_tukar' => now()->toDateString(),
            'no_kwitansi' => "KW-UJI-{$urutan}",
            'jumlah_rupiah' => 1000000,
            'nama_pic' => 'PIC Uji',
            'email_penerima' => 'pic@vendor.test',
            'status' => 'pending',
        ]);
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get('/admin/tukar-faktur')->assertRedirect(route('login'));
    }

    /** Semua peran boleh melihat daftar tukar faktur. */
    public function test_semua_peran_bisa_melihat_daftar_tukar_faktur(): void
    {
        foreach (UserRole::cases() as $role) {
            $this->actingAs($this->user($role))
                ->get('/admin/tukar-faktur')
                ->assertOk();
        }
    }

    public function test_verifikator_dan_billing_tidak_bisa_mengisi_tanggal_pembayaran(): void
    {
        foreach ([UserRole::Verifikator, UserRole::Billing] as $role) {
            $data = $this->tukarFaktur();

            $this->actingAs($this->user($role))
                ->post(route('admin.tukar-faktur.payment-date', $data->id), [
                    'tanggal_pembayaran' => now()->toDateString(),
                ])
                ->assertForbidden();

            $this->assertNull($data->fresh()->tanggal_pembayaran);
        }
    }

    public function test_kontrabon_dan_admin_bisa_mengisi_tanggal_pembayaran(): void
    {
        // Jalur sukses ikut membuat PDF lewat dompdf yang memerlukan ekstensi
        // GD — tersedia di image Docker, belum tentu di mesin lokal.
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('Ekstensi GD tidak terpasang; dompdf tidak bisa membuat PDF.');
        }

        // PDF dan email dipalsukan supaya test tidak menulis file atau
        // mengirim email sungguhan.
        Storage::fake();
        Mail::fake();

        foreach ([UserRole::Admin, UserRole::Kontrabon] as $role) {
            $data = $this->tukarFaktur();

            $this->actingAs($this->user($role))
                ->post(route('admin.tukar-faktur.payment-date', $data->id), [
                    'tanggal_pembayaran' => now()->toDateString(),
                ])
                ->assertRedirect();

            $this->assertNotNull($data->fresh()->tanggal_pembayaran);
        }
    }

    public function test_verifikator_dan_billing_tidak_bisa_menghapus_tukar_faktur(): void
    {
        foreach ([UserRole::Verifikator, UserRole::Billing] as $role) {
            $data = $this->tukarFaktur();

            $this->actingAs($this->user($role))
                ->delete(route('admin.tukar-faktur.destroy', $data->id))
                ->assertForbidden();

            $this->assertDatabaseHas('tukar_fakturs', ['id' => $data->id]);
        }
    }

    public function test_master_perusahaan_hanya_untuk_kontrabon_dan_admin(): void
    {
        foreach ([UserRole::Admin, UserRole::Kontrabon] as $role) {
            $this->actingAs($this->user($role))
                ->get('/admin/perusahaan')
                ->assertOk();
        }

        foreach ([UserRole::Verifikator, UserRole::Billing] as $role) {
            $this->actingAs($this->user($role))
                ->get('/admin/perusahaan')
                ->assertForbidden();
        }
    }

    public function test_manajemen_pengguna_hanya_untuk_admin(): void
    {
        $this->actingAs($this->user(UserRole::Admin))
            ->get('/admin/users')
            ->assertOk();

        foreach ([UserRole::Kontrabon, UserRole::Verifikator, UserRole::Billing] as $role) {
            $this->actingAs($this->user($role))
                ->get('/admin/users')
                ->assertForbidden();

            $this->actingAs($this->user($role))
                ->get('/admin/users/create')
                ->assertForbidden();
        }
    }

    public function test_user_nonaktif_ditolak_walau_sesinya_masih_hidup(): void
    {
        $user = User::factory()->role(UserRole::Kontrabon)->inactive()->create();

        $this->actingAs($user)
            ->get('/admin/tukar-faktur')
            ->assertForbidden();
    }
}
