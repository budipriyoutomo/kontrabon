<?php

declare(strict_types=1);

namespace App\View\Variants;

use App\Enums\TukarFakturStatus;
use App\Enums\UserRole;
use App\Support\Cva;

/**
 * Varian badge, mengikuti anatomi badge shadcn/ui.
 *
 * Selain varian standar, di sini juga dipetakan status tukar faktur ke varian
 * warnanya supaya seluruh halaman menampilkan status yang sama dengan warna
 * yang sama.
 */
final class BadgeVariants
{
    public static function cva(): Cva
    {
        return Cva::make(
            base: 'inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-semibold '
                .'transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 '
                .'[&_svg]:size-3 [&_svg]:shrink-0',
            variants: [
                'variant' => [
                    'default' => 'border-transparent bg-primary text-primary-foreground',
                    'secondary' => 'border-transparent bg-secondary text-secondary-foreground',
                    'destructive' => 'border-transparent bg-destructive text-destructive-foreground',
                    'outline' => 'text-foreground',
                    // Varian lunak dipakai untuk status pada tabel: warnanya
                    // cukup jelas dibedakan tapi tidak seramai badge solid.
                    'success' => 'border-success/20 bg-success/10 text-success',
                    'warning' => 'border-warning/30 bg-warning/10 text-warning',
                    'info' => 'border-info/20 bg-info/10 text-info',
                    'muted' => 'border-transparent bg-muted text-muted-foreground',
                ],
            ],
            defaultVariants: [
                'variant' => 'default',
            ],
        );
    }

    public static function classes(?string $variant = null): string
    {
        return self::cva()->resolve(['variant' => $variant]);
    }

    /**
     * Varian warna untuk tiap peran pengguna.
     *
     * Dibedakan hanya agar peran mudah dipindai di tabel; tidak ada urutan
     * atau tingkatan yang tersirat dari warnanya.
     */
    public static function forRole(UserRole|string|null $role): string
    {
        $value = $role instanceof UserRole ? $role->value : $role;

        return match ($value) {
            UserRole::Admin->value => 'default',
            UserRole::Kontrabon->value => 'info',
            UserRole::Verifikator->value => 'warning',
            UserRole::Billing->value => 'success',
            default => 'muted',
        };
    }

    /**
     * Varian warna untuk tiap status tukar faktur.
     *
     * Alurnya pending -> email_sent -> verified -> billing, jadi warnanya
     * dibuat menaik: netral, informatif, lalu positif.
     */
    public static function forStatus(TukarFakturStatus|string|null $status): string
    {
        $value = $status instanceof TukarFakturStatus ? $status->value : $status;

        return match ($value) {
            TukarFakturStatus::Pending->value => 'warning',
            TukarFakturStatus::EmailSent->value => 'info',
            TukarFakturStatus::Verified->value => 'success',
            TukarFakturStatus::Billing->value => 'default',
            default => 'muted',
        };
    }
}
