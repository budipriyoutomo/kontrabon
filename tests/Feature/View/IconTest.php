<?php

declare(strict_types=1);

namespace Tests\Feature\View;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class IconTest extends TestCase
{
    public function test_merender_svg_lucide(): void
    {
        $html = Blade::render('<x-icon name="funnel" />');

        $this->assertStringContainsString('<svg', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);
        $this->assertStringContainsString('size-4', $html);
    }

    public function test_ukuran_bawaan_bisa_ditimpa_tanpa_menyisakan_kelas_lama(): void
    {
        $html = Blade::render('<x-icon name="funnel" class="size-6 text-muted-foreground" />');

        $this->assertStringContainsString('size-6', $html);
        $this->assertStringNotContainsString('size-4', $html);
        $this->assertStringContainsString('text-muted-foreground', $html);
    }
}
