<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Perusahaan;
use App\Models\User;

/**
 * Master data supplier hanya diurus kontrabon (dan admin lewat Gate::before).
 * Verifikator maupun billing tidak perlu menyentuh master ini.
 */
class PerusahaanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::Kontrabon);
    }

    public function view(User $user, Perusahaan $perusahaan): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::Kontrabon);
    }

    public function update(User $user, Perusahaan $perusahaan): bool
    {
        return $user->hasRole(UserRole::Kontrabon);
    }

    public function delete(User $user, Perusahaan $perusahaan): bool
    {
        return $user->hasRole(UserRole::Kontrabon);
    }
}
