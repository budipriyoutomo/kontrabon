<?php

namespace App\Policies;

use App\Models\User;

/**
 * Manajemen pengguna sepenuhnya milik admin, yang sudah diloloskan oleh
 * Gate::before. Method di bawah menutup akses untuk peran lain sekaligus
 * menjaga admin agar tidak mengunci dirinya sendiri.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, User $target): bool
    {
        return false;
    }

    /**
     * Admin sekalipun tidak boleh menghapus akunnya sendiri.
     */
    public function delete(User $user, User $target): bool
    {
        return $user->isAdmin() && $user->id !== $target->id;
    }

    /**
     * Menonaktifkan akun sendiri = terkunci dari aplikasi.
     */
    public function toggleActive(User $user, User $target): bool
    {
        return $user->isAdmin() && $user->id !== $target->id;
    }
}
