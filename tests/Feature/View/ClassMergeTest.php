<?php

declare(strict_types=1);

namespace Tests\Feature\View;

use App\Support\Cva;
use Illuminate\View\ComponentAttributeBag;
use Tests\TestCase;

/**
 * Menjaga kontrak dasar penggayaan komponen: varian dipilih oleh Cva, lalu
 * class dari pemakai komponen menimpa class varian lewat tailwind-merge.
 */
class ClassMergeTest extends TestCase
{
    public function test_class_dari_pemakai_menimpa_class_bawaan_komponen(): void
    {
        $attributes = new ComponentAttributeBag(['class' => 'px-8 bg-destructive']);

        $this->assertSame(
            'inline-flex py-2 px-8 bg-destructive',
            $attributes->twMerge('inline-flex px-4 py-2 bg-primary')->get('class'),
        );
    }

    public function test_class_bawaan_dipertahankan_saat_pemakai_tidak_mengirim_class(): void
    {
        $attributes = new ComponentAttributeBag();

        $this->assertSame(
            'inline-flex px-4 py-2',
            $attributes->twMerge('inline-flex px-4 py-2')->get('class'),
        );
    }

    public function test_varian_responsif_dan_state_tidak_saling_menimpa(): void
    {
        $attributes = new ComponentAttributeBag(['class' => 'md:px-8']);

        $this->assertSame(
            'px-4 hover:px-6 md:px-8',
            $attributes->twMerge('px-4 hover:px-6 md:px-2')->get('class'),
        );
    }

    public function test_hasil_cva_ikut_diselesaikan_konfliknya_oleh_tw_merge(): void
    {
        $variants = Cva::make(
            base: 'inline-flex rounded-md',
            variants: ['size' => ['default' => 'h-9 px-4']],
            defaultVariants: ['size' => 'default'],
        );

        $attributes = new ComponentAttributeBag(['class' => 'h-12']);

        $this->assertSame(
            'inline-flex rounded-md px-4 h-12',
            $attributes->twMerge($variants->resolve())->get('class'),
        );
    }
}
