<?php

namespace Tests\Unit\Services;

use App\Services\ScoreService;
use Illuminate\Support\Collection;
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

    public function test_get_score_breakdown_returns_array(): void
    {
        $result = $this->service->getScoreBreakdown(
            collect(),
            collect(),
            collect()
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('dpl', $result);
        $this->assertArrayHasKey('desa', $result);
        $this->assertArrayHasKey('lppm', $result);
        $this->assertArrayHasKey('total', $result);
    }

    public function test_get_score_breakdown_with_empty_collections(): void
    {
        $result = $this->service->getScoreBreakdown(
            collect(),
            collect(),
            collect()
        );

        $this->assertNull($result['dpl']);
        $this->assertNull($result['desa']);
        $this->assertNull($result['lppm']);
        $this->assertNull($result['total']);
    }

    public function test_get_score_breakdown_with_valid_scores(): void
    {
        $komponenList = new Collection([
            (object) ['id' => 1, 'nama_komponen' => 'Nilai DPL'],
            (object) ['id' => 2, 'nama_komponen' => 'Nilai Desa'],
            (object) ['id' => 3, 'nama_komponen' => 'Nilai LPPM'],
        ]);

        $penilaianIndividu = new Collection([
            (object) ['komponen_id' => 1, 'nilai' => 80],
            (object) ['komponen_id' => 1, 'nilai' => 80],
            (object) ['komponen_id' => 2, 'nilai' => 70],
            (object) ['komponen_id' => 2, 'nilai' => 70],
        ]);

        $penilaianKelompok = new Collection([
            new class {
                public $nilai = 90;
                public $komponen;
                public function __construct()
                {
                    $this->komponen = (object) ['nama_komponen' => 'Nilai LPPM'];
                }
            },
        ]);

        $result = $this->service->getScoreBreakdown($penilaianIndividu, $penilaianKelompok, $komponenList);

        $this->assertEqualsWithDelta(80.0, $result['dpl'], 0.01);
        $this->assertEqualsWithDelta(70.0, $result['desa'], 0.01);
        $this->assertEqualsWithDelta(90.0, $result['lppm'], 0.01);
        // 80 * 0.40 + 70 * 0.30 + 90 * 0.30 = 32 + 21 + 27 = 80
        $this->assertEqualsWithDelta(80.0, $result['total'], 0.01);
    }

    public function test_get_score_breakdown_with_partial_scores(): void
    {
        $komponenList = new Collection([
            (object) ['id' => 1, 'nama_komponen' => 'Nilai DPL'],
            (object) ['id' => 2, 'nama_komponen' => 'Nilai Desa'],
            (object) ['id' => 3, 'nama_komponen' => 'Nilai LPPM'],
        ]);

        $penilaianIndividu = new Collection([
            (object) ['komponen_id' => 1, 'nilai' => 80],
        ]);

        $penilaianKelompok = new Collection();

        $result = $this->service->getScoreBreakdown($penilaianIndividu, $penilaianKelompok, $komponenList);

        $this->assertEqualsWithDelta(80.0, $result['dpl'], 0.01);
        $this->assertNull($result['desa']);
        $this->assertNull($result['lppm']);
        $this->assertNull($result['total']);
    }

    public function test_constants_are_defined(): void
    {
        $this->assertArrayHasKey('dpl', ScoreService::WEIGHTS);
        $this->assertArrayHasKey('desa', ScoreService::WEIGHTS);
        $this->assertArrayHasKey('lppm', ScoreService::WEIGHTS);
        $this->assertEquals(0.40, ScoreService::WEIGHTS['dpl']);
        $this->assertEquals(0.30, ScoreService::WEIGHTS['desa']);
        $this->assertEquals(0.30, ScoreService::WEIGHTS['lppm']);
    }
}
