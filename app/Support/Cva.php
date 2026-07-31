<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

/**
 * Padanan class-variance-authority (CVA) untuk komponen Blade.
 *
 * Tugasnya hanya memilih string class berdasarkan varian yang dipilih.
 * Penggabungan dengan class dari pemakai komponen diserahkan ke tailwind-merge
 * lewat macro $attributes->twMerge(), supaya konflik seperti px-4 vs px-8
 * diselesaikan dengan benar.
 *
 * Definisi varian tiap komponen disimpan di App\View\Variants.
 */
final class Cva
{
    /**
     * @param  array<string, array<string, string>>  $variants
     * @param  array<string, string|bool|null>  $defaultVariants
     * @param  list<array<string, string|bool|null>>  $compoundVariants
     */
    public function __construct(
        private readonly string $base = '',
        private readonly array $variants = [],
        private readonly array $defaultVariants = [],
        private readonly array $compoundVariants = [],
    ) {
    }

    /**
     * @param  array<string, array<string, string>>  $variants
     * @param  array<string, string|bool|null>  $defaultVariants
     * @param  list<array<string, string|bool|null>>  $compoundVariants
     */
    public static function make(
        string $base = '',
        array $variants = [],
        array $defaultVariants = [],
        array $compoundVariants = [],
    ): self {
        return new self($base, $variants, $defaultVariants, $compoundVariants);
    }

    /**
     * Rangkai class akhir untuk kombinasi varian yang dipilih.
     *
     * Nilai null berarti "pakai default". Nilai yang tidak dikenal sengaja
     * dilempar sebagai exception supaya salah ketik pada props komponen
     * ketahuan saat render, bukan diam-diam menghasilkan komponen tanpa gaya.
     *
     * @param  array<string, string|bool|null>  $selected
     */
    public function resolve(array $selected = []): string
    {
        $unknown = array_diff(array_keys($selected), array_keys($this->variants));

        if ($unknown !== []) {
            throw new InvalidArgumentException(sprintf(
                'Varian tidak dikenal: %s. Varian yang tersedia: %s.',
                implode(', ', $unknown),
                implode(', ', array_keys($this->variants)) ?: '(tidak ada)',
            ));
        }

        $resolved = $this->resolveValues($selected);

        $classes = [$this->base];

        foreach ($this->variants as $variant => $options) {
            $value = $resolved[$variant] ?? null;

            if ($value === null) {
                continue;
            }

            if (! array_key_exists($value, $options)) {
                throw new InvalidArgumentException(sprintf(
                    'Nilai "%s" tidak tersedia untuk varian "%s". Pilihan: %s.',
                    $value,
                    $variant,
                    implode(', ', array_keys($options)),
                ));
            }

            $classes[] = $options[$value];
        }

        foreach ($this->compoundVariants as $compound) {
            $extra = $compound['class'] ?? '';
            unset($compound['class']);

            if ($this->matchesCompound($compound, $resolved)) {
                $classes[] = $extra;
            }
        }

        return $this->join($classes);
    }

    /**
     * Gabungan default varian dengan pilihan pemakai, sudah dinormalkan.
     *
     * @param  array<string, string|bool|null>  $selected
     * @return array<string, string|null>
     */
    private function resolveValues(array $selected): array
    {
        $resolved = [];

        foreach (array_keys($this->variants) as $variant) {
            $value = $selected[$variant] ?? null;

            if ($value === null) {
                $value = $this->defaultVariants[$variant] ?? null;
            }

            $resolved[$variant] = $value === null ? null : self::normalize($value);
        }

        return $resolved;
    }

    /**
     * @param  array<string, string|bool|null>  $compound
     * @param  array<string, string|null>  $resolved
     */
    private function matchesCompound(array $compound, array $resolved): bool
    {
        foreach ($compound as $variant => $expected) {
            if ($expected === null) {
                continue;
            }

            if (($resolved[$variant] ?? null) !== self::normalize($expected)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Prop boolean dari Blade dinormalkan ke string "true"/"false".
     *
     * Definisi varian karena itu harus memakai kunci string 'true'/'false',
     * bukan true/false literal — PHP akan mengubahnya jadi kunci 1/0.
     */
    private static function normalize(string|bool $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return $value;
    }

    /**
     * @param  list<string>  $classes
     */
    private function join(array $classes): string
    {
        $normalized = preg_replace('/\s+/', ' ', implode(' ', $classes)) ?? '';

        return trim($normalized);
    }
}
