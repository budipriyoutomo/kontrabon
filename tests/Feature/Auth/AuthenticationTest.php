<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->role(UserRole::Kontrabon)->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.tukar-faktur.index'));
    }

    public function test_each_role_lands_on_its_own_home_page(): void
    {
        $harapan = [
            UserRole::Admin->value => route('admin.tukar-faktur.index'),
            UserRole::Kontrabon->value => route('admin.tukar-faktur.index'),
            UserRole::Verifikator->value => route('admin.verifikasi.index'),
            UserRole::Billing->value => route('billing.index'),
        ];

        foreach ($harapan as $role => $tujuan) {
            $user = User::factory()->role(UserRole::from($role))->create();

            $this->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ])->assertRedirect($tujuan);

            $this->post('/logout');
        }
    }

    public function test_inactive_users_can_not_authenticate(): void
    {
        $user = User::factory()->inactive()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
