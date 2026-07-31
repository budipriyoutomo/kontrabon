<?php

namespace App\Providers;

use App\Models\Perusahaan;
use App\Models\TukarFaktur;
use App\Models\User;
use App\Policies\PerusahaanPolicy;
use App\Policies\TukarFakturPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        TukarFaktur::class => TukarFakturPolicy::class,
        Perusahaan::class => PerusahaanPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Admin lolos semua ability, dengan satu pengecualian: aksi yang
        // menyasar akunnya sendiri (hapus / nonaktifkan) diteruskan ke
        // UserPolicy supaya admin tidak bisa mengunci dirinya sendiri.
        Gate::before(function (User $user, string $ability, array $arguments = []) {
            if (! $user->isAdmin()) {
                return null;
            }

            $target = $arguments[0] ?? null;

            if ($target instanceof User
                && $target->id === $user->id
                && in_array($ability, ['delete', 'toggleActive'], true)) {
                return null;
            }

            return true;
        });
    }
}
