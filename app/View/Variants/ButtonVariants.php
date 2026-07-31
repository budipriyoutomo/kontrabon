<?php

declare(strict_types=1);

namespace App\View\Variants;

use App\Support\Cva;

/**
 * Varian tombol, mengikuti anatomi tombol shadcn/ui.
 *
 * Disimpan terpisah dari komponen Blade supaya elemen lain yang harus tampil
 * seperti tombol — misalnya <a> dan tombol paginasi — bisa memakai definisi
 * yang sama persis.
 */
final class ButtonVariants
{
    public static function cva(): Cva
    {
        return Cva::make(
            base: 'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium '
                .'ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 '
                .'focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none '
                .'disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0',
            variants: [
                'variant' => [
                    'default' => 'bg-primary text-primary-foreground hover:bg-primary/90',
                    'destructive' => 'bg-destructive text-destructive-foreground hover:bg-destructive/90',
                    'outline' => 'border border-input bg-background hover:bg-accent hover:text-accent-foreground',
                    'secondary' => 'bg-secondary text-secondary-foreground hover:bg-secondary/80',
                    'ghost' => 'hover:bg-accent hover:text-accent-foreground',
                    'link' => 'text-primary underline-offset-4 hover:underline',
                    // Di luar palet shadcn, dipakai aksi bernuansa positif
                    // seperti Export Excel dan tombol verifikasi.
                    'success' => 'bg-success text-success-foreground hover:bg-success/90',
                ],
                'size' => [
                    'default' => 'h-9 px-4 py-2',
                    'sm' => 'h-8 rounded-md px-3 text-xs',
                    'lg' => 'h-10 rounded-md px-8',
                    'icon' => 'size-9',
                ],
            ],
            defaultVariants: [
                'variant' => 'default',
                'size' => 'default',
            ],
        );
    }

    /**
     * @param  string|null  $variant  default|destructive|outline|secondary|ghost|link|success
     * @param  string|null  $size  default|sm|lg|icon
     */
    public static function classes(?string $variant = null, ?string $size = null): string
    {
        return self::cva()->resolve(['variant' => $variant, 'size' => $size]);
    }
}
