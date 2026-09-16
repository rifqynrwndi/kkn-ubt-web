<?php

namespace Tests\Unit\Services;

use App\Services\ExportService;
use Tests\TestCase;

class ExportServiceTest extends TestCase
{
    private ExportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ExportService;
    }

    public function test_export_service_can_be_instantiated(): void
    {
        $this->assertInstanceOf(ExportService::class, $this->service);
    }

    public function test_export_methods_exist(): void
    {
        $this->assertTrue(method_exists($this->service, 'exportKelompokXlsx'));
        $this->assertTrue(method_exists($this->service, 'exportTugasXlsx'));
        $this->assertTrue(method_exists($this->service, 'exportMahasiswaXlsx'));
    }
}
