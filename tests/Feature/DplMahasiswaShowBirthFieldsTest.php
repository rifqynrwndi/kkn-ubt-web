<?php

namespace Tests\Feature;

use Tests\TestCase;

class DplMahasiswaShowBirthFieldsTest extends TestCase
{
    public function test_dpl_mahasiswa_show_view_contains_birth_fields(): void
    {
        $blade = file_get_contents(resource_path('views/dpl/mahasiswa-show.blade.php'));

        $this->assertStringContainsString('Tempat Lahir', $blade);
        $this->assertStringContainsString('Tanggal Lahir', $blade);
        $this->assertStringContainsString('birth_place', $blade);
        $this->assertStringContainsString('birth_date', $blade);
    }
}
