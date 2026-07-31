<?php

declare(strict_types=1);

namespace App\View\Variants;

use App\Support\Cva;

/**
 * Varian alert, mengikuti anatomi alert shadcn/ui.
 *
 * Selector [&>svg] dipakai agar ikon opsional di dalam alert diposisikan dan
 * diwarnai otomatis tanpa perlu class tambahan di tempat pemakaian.
 */
final class AlertVariants
{
    public static function cva(): Cva
    {
        return Cva::make(
            base: 'relative w-full rounded-lg border px-4 py-3 text-sm [&>svg]:absolute [&>svg]:left-4 '
                .'[&>svg]:top-4 [&>svg]:size-4 [&>svg~*]:pl-7',
            variants: [
                'variant' => [
                    'default' => 'bg-background text-foreground [&>svg]:text-foreground',
                    'destructive' => 'border-destructive/50 bg-destructive/5 text-destructive [&>svg]:text-destructive',
                    'success' => 'border-success/30 bg-success/5 text-success [&>svg]:text-success',
                    'warning' => 'border-warning/40 bg-warning/5 text-warning [&>svg]:text-warning',
                    'info' => 'border-info/30 bg-info/5 text-info [&>svg]:text-info',
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
}
