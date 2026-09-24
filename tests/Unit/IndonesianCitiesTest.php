<?php

namespace Tests\Unit;

use App\Helpers\IndonesianCities;
use Tests\TestCase;

class IndonesianCitiesTest extends TestCase
{
    public function test_all_returns_array_of_city_names(): void
    {
        $cities = IndonesianCities::all();

        $this->assertIsArray($cities);
        $this->assertNotEmpty($cities);
        $this->assertContains('Tarakan', $cities);
    }

    public function test_all_with_provinces_returns_grouped_data(): void
    {
        $grouped = IndonesianCities::allWithProvinces();

        $this->assertIsArray($grouped);
        $this->assertArrayHasKey('Kalimantan Utara', $grouped);
        $this->assertContains('Tarakan', $grouped['Kalimantan Utara']);
    }

    public function test_provinces_returns_province_list(): void
    {
        $provinces = IndonesianCities::provinces();

        $this->assertIsArray($provinces);
        $this->assertArrayHasKey(65, $provinces);
        $this->assertEquals('Kalimantan Utara', $provinces[65]);
    }

    public function test_search_finds_cities(): void
    {
        $results = IndonesianCities::search('tarakan');

        $this->assertContains('Tarakan', $results);
    }

    public function test_search_returns_empty_for_no_match(): void
    {
        $results = IndonesianCities::search('xyznonexistent');

        $this->assertEmpty($results);
    }
}
