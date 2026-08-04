<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_admin_bisa_membuat_pengguna_baru(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.users.store'), [
                'name' => 'Staff Billing',
                'email' => 'billing.baru@maharasa.test',
                'role' => UserRole::Billing->value,
                'is_active' => '1',
                'password' => 'rahasia-kuat-123',
                'password_confirmation' => 'rahasia-kuat-123',
            ])
            ->assertRedirect(route('admin.users.index'));

        $baru = User::where('email', 'billing.baru@maharasa.test')->first();

        $this->assertNotNull($baru);
        $this->assertSame(UserRole::Billing, $baru->role);
        $this->assertTrue($baru->is_active);
        $this->assertTrue(Hash::check('rahasia-kuat-123', $baru->password));
    }

    public function test_edit_tanpa_password_tidak_mengubah_password(): void
    {
        $admin = $this->admin();
        $user = User::factory()->role(UserRole::Kontrabon)->create();
        $passwordLama = $user->password;

        $this->actingAs($admin)
            ->put(route('admin.users.update', $user->id), [
                'name' => 'Nama Baru',
                'email' => $user->email,
                'role' => UserRole::Verifikator->value,
                'is_active' => '1',
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertRedirect(route('admin.users.index'));

        $user->refresh();

        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame(UserRole::Verifikator, $user->role);
        $this->assertSame($passwordLama, $user->password);
    }

    public function test_admin_bisa_menonaktifkan_pengguna_lain(): void
    {
        $user = User::factory()->role(UserRole::Kontrabon)->create();

        $this->actingAs($this->admin())
            ->put(route('admin.users.toggle-active', $user->id))
            ->assertRedirect();

        $this->assertFalse($user->fresh()->is_active);
    }

    public function test_admin_tidak_bisa_menonaktifkan_akun_sendiri(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put(route('admin.users.toggle-active', $admin->id))
            ->assertRedirect();

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_admin_tidak_bisa_menghapus_akun_sendiri(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin->id))
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_bisa_reset_password_pengguna(): void
    {
        $user = User::factory()->role(UserRole::Billing)->create();

        $this->actingAs($this->admin())
            ->put(route('admin.users.reset-password', $user->id), [
                'password' => 'password-baru-456',
                'password_confirmation' => 'password-baru-456',
            ])
            ->assertRedirect();

        $this->assertTrue(Hash::check('password-baru-456', $user->fresh()->password));
    }

    public function test_daftar_pengguna_menampilkan_form_buat_password(): void
    {
        $user = User::factory()->role(UserRole::Billing)->create();

        $this->actingAs($this->admin())
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee(route('admin.users.reset-password', $user->id), escape: false)
            ->assertSee('Buat Password Baru');
    }

    public function test_reset_password_gagal_masuk_error_bag_pengguna_terkait(): void
    {
        $user = User::factory()->role(UserRole::Billing)->create();
        $passwordLama = $user->password;

        $this->actingAs($this->admin())
            ->from(route('admin.users.index'))
            ->put(route('admin.users.reset-password', $user->id), [
                'password' => 'password-baru-456',
                'password_confirmation' => 'beda-sendiri',
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHasErrors('password', errorBag: 'resetPassword' . $user->id);

        $this->assertSame($passwordLama, $user->fresh()->password);
    }

    public function test_admin_tidak_bisa_mengubah_peran_akun_sendiri(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put(route('admin.users.update', $admin->id), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => UserRole::Billing->value,
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('role');

        $this->assertSame(UserRole::Admin, $admin->fresh()->role);
    }

    public function test_email_berhuruf_kapital_dinormalkan(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.users.store'), [
                'name' => 'Huruf Kapital',
                'email' => '  Staff.Baru@Maharasa.Test ',
                'role' => UserRole::Kontrabon->value,
                'is_active' => '1',
                'password' => 'rahasia-kuat-123',
                'password_confirmation' => 'rahasia-kuat-123',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', ['email' => 'staff.baru@maharasa.test']);
    }

    public function test_pengguna_baru_yang_kotak_aktifnya_dicentang_bisa_login(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.users.store'), [
                'name' => 'Staff Aktif',
                'email' => 'staff.aktif@maharasa.test',
                'role' => UserRole::Kontrabon->value,
                'is_active' => '1',
                'password' => 'rahasia-kuat-123',
                'password_confirmation' => 'rahasia-kuat-123',
            ]);

        $this->assertTrue(User::where('email', 'staff.aktif@maharasa.test')->first()->is_active);

        $this->post(route('login'), [
            'email' => 'staff.aktif@maharasa.test',
            'password' => 'rahasia-kuat-123',
        ]);

        $this->assertAuthenticated();
    }

    public function test_email_harus_unik(): void
    {
        $existing = User::factory()->create(['email' => 'dipakai@maharasa.test']);

        $this->actingAs($this->admin())
            ->post(route('admin.users.store'), [
                'name' => 'Duplikat',
                'email' => $existing->email,
                'role' => UserRole::Kontrabon->value,
                'password' => 'rahasia-kuat-123',
                'password_confirmation' => 'rahasia-kuat-123',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_peran_di_luar_daftar_ditolak(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.users.store'), [
                'name' => 'Peran Aneh',
                'email' => 'aneh@maharasa.test',
                'role' => 'superuser',
                'password' => 'rahasia-kuat-123',
                'password_confirmation' => 'rahasia-kuat-123',
            ])
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'aneh@maharasa.test']);
    }
}
