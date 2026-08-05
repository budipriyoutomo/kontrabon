<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\TukarFaktur;
use App\Models\User;

/**
 * Admin tidak diperiksa di sini — Gate::before pada AuthServiceProvider
 * sudah meloloskannya untuk seluruh ability.
 */
class TukarFakturPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(
            UserRole::Kontrabon,
            UserRole::Verifikator,
            UserRole::Billing,
        );
    }

    public function view(User $user, TukarFaktur $tukarFaktur): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::Kontrabon);
    }

    public function update(User $user, TukarFaktur $tukarFaktur): bool
    {
        return $user->hasRole(UserRole::Kontrabon);
    }

    public function delete(User $user, TukarFaktur $tukarFaktur): bool
    {
        return $user->hasRole(UserRole::Kontrabon);
    }

    /**
     * Pengisian tanggal pembayaran memicu pengiriman email ke supplier,
     * jadi hanya kontrabon yang boleh.
     */
    public function setPaymentDate(User $user, TukarFaktur $tukarFaktur): bool
    {
        return $user->hasRole(UserRole::Kontrabon);
    }

    /**
     * Mengirim ulang bukti yang sudah pernah terkirim, misalnya saat supplier
     * kehilangan emailnya. Sama seperti pengiriman pertama, ini urusan
     * kontrabon.
     */
    public function resendEmail(User $user, TukarFaktur $tukarFaktur): bool
    {
        return $user->hasRole(UserRole::Kontrabon);
    }

    /**
     * Verifikasi dilakukan setelah email bukti terkirim ke supplier.
     */
    public function verify(User $user, TukarFaktur $tukarFaktur): bool
    {
        return $user->hasRole(UserRole::Verifikator);
    }

    /** Akses modul billing (rekap pembayaran atas data terverifikasi). */
    public function viewBilling(User $user): bool
    {
        return $user->hasRole(UserRole::Billing);
    }

    /** Menandai data terverifikasi sebagai sudah masuk proses billing. */
    public function processBilling(User $user, TukarFaktur $tukarFaktur): bool
    {
        return $user->hasRole(UserRole::Billing);
    }
}
