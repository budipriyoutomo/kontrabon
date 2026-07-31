<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\Cva;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CvaTest extends TestCase
{
    private function tombol(): Cva
    {
        return Cva::make(
            base: 'inline-flex items-center',
            variants: [
                'variant' => [
                    'default' => 'bg-primary text-primary-foreground',
                    'outline' => 'border border-input bg-background',
                ],
                'size' => [
                    'sm' => 'h-8 px-3',
                    'default' => 'h-9 px-4',
                ],
            ],
            defaultVariants: [
                'variant' => 'default',
                'size' => 'default',
            ],
        );
    }

    public function test_memakai_varian_default_saat_tidak_ada_pilihan(): void
    {
        $this->assertSame(
            'inline-flex items-center bg-primary text-primary-foreground h-9 px-4',
            $this->tombol()->resolve(),
        );
    }

    public function test_pilihan_varian_menimpa_default(): void
    {
        $this->assertSame(
            'inline-flex items-center border border-input bg-background h-8 px-3',
            $this->tombol()->resolve(['variant' => 'outline', 'size' => 'sm']),
        );
    }

    public function test_nilai_null_jatuh_ke_default(): void
    {
        $this->assertSame(
            $this->tombol()->resolve(),
            $this->tombol()->resolve(['variant' => null, 'size' => null]),
        );
    }

    public function test_urutan_class_mengikuti_urutan_definisi_varian_bukan_urutan_pilihan(): void
    {
        $this->assertSame(
            $this->tombol()->resolve(['variant' => 'outline', 'size' => 'sm']),
            $this->tombol()->resolve(['size' => 'sm', 'variant' => 'outline']),
        );
    }

    public function test_varian_tanpa_default_diabaikan_saat_tidak_dipilih(): void
    {
        $cva = Cva::make(
            base: 'rounded-md',
            variants: ['tone' => ['muted' => 'text-muted-foreground']],
        );

        $this->assertSame('rounded-md', $cva->resolve());
    }

    public function test_compound_variant_menambah_class_saat_semua_syarat_cocok(): void
    {
        $cva = Cva::make(
            base: 'inline-flex',
            variants: [
                'variant' => ['outline' => 'border', 'default' => 'bg-primary'],
                'size' => ['icon' => 'size-9', 'default' => 'h-9'],
            ],
            defaultVariants: ['variant' => 'default', 'size' => 'default'],
            compoundVariants: [
                ['variant' => 'outline', 'size' => 'icon', 'class' => 'p-0'],
            ],
        );

        $this->assertSame('inline-flex border size-9 p-0', $cva->resolve(['variant' => 'outline', 'size' => 'icon']));
        $this->assertSame('inline-flex border h-9', $cva->resolve(['variant' => 'outline']));
    }

    public function test_compound_variant_cocok_lewat_nilai_default(): void
    {
        $cva = Cva::make(
            variants: [
                'variant' => ['default' => 'bg-primary'],
                'size' => ['icon' => 'size-9'],
            ],
            defaultVariants: ['variant' => 'default'],
            compoundVariants: [
                ['variant' => 'default', 'size' => 'icon', 'class' => 'p-0'],
            ],
        );

        $this->assertSame('bg-primary size-9 p-0', $cva->resolve(['size' => 'icon']));
    }

    public function test_prop_boolean_dipetakan_ke_kunci_string(): void
    {
        $cva = Cva::make(
            base: 'flex',
            variants: [
                'aktif' => ['true' => 'bg-accent', 'false' => 'text-muted-foreground'],
            ],
        );

        $this->assertSame('flex bg-accent', $cva->resolve(['aktif' => true]));
        $this->assertSame('flex text-muted-foreground', $cva->resolve(['aktif' => false]));
    }

    public function test_nama_varian_tak_dikenal_dilempar(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Varian tidak dikenal: varian');

        $this->tombol()->resolve(['varian' => 'outline']);
    }

    public function test_nilai_varian_tak_dikenal_dilempar(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Nilai "destructve" tidak tersedia untuk varian "variant"');

        $this->tombol()->resolve(['variant' => 'destructve']);
    }

    public function test_spasi_berlebih_dirapikan(): void
    {
        $cva = Cva::make(
            base: "inline-flex\n    items-center",
            variants: ['size' => ['sm' => '  h-8   px-3  ']],
            defaultVariants: ['size' => 'sm'],
        );

        $this->assertSame('inline-flex items-center h-8 px-3', $cva->resolve());
    }
}
