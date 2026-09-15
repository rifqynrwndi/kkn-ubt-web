<?php

namespace Tests\Unit\Services;

use App\Services\ScoreService;
use Tests\TestCase;

class ScoreServiceTest extends TestCase
{
    private ScoreService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ScoreService;
    }

    public function test_score_service_can_be_instantiated(): void
    {
        $this->assertInstanceOf(ScoreService::class, $this->service);
    }

    public function test_score_methods_exist(): void
    {
        $this->assertTrue(method_exists($this->service, 'calculateDplScore'));
        $this->assertTrue(method_exists($this->service, 'calculateDesaScore'));
        $this->assertTrue(method_exists($this->service, 'calculateLppmScore'));
        $this->assertTrue(method_exists($this->service, 'calculateTotal'));
    }

    public function test_calculate_total_with_valid_scores(): void
    {
        // 80 * 0.40 + 70 * 0.30 + 90 * 0.30 = 32 + 21 + 27 = 80
        $result = $this->service->calculateTotal(80.0, 70.0, 90.0);
        $this->assertEqualsWithDelta(80.0, $result, 0.01);
    }

    public function test_calculate_total_with_null_scores(): void
    {
        $this->assertNull($this->service->calculateTotal(null, 70.0, 90.0));
        $this->assertNull($this->service->calculateTotal(80.0, null, 90.0));
        $this->assertNull($this->service->calculateTotal(80.0, 70.0, null));
    }

    public function test_calculate_total_with_all_null(): void
    {
        $this->assertNull($this->service->calculateTotal(null, null, null));
    }

    public function test_calculate_total_weights(): void
    {
        // DPL 60% (40% + 20% split), Desa 30%, LPPM 40%
        // But the formula is: dpl * 0.40 + desa * 0.30 + lppm * 0.30
        $result = $this->service->calculateTotal(100.0, 100.0, 100.0);
        $this->assertEqualsWithDelta(100.0, $result, 0.01);

        $result = $this->service->calculateTotal(0.0, 0.0, 0.0);
        $this->assertEqualsWithDelta(0.0, $result, 0.01);
    }
}
