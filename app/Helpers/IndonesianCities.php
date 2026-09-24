<?php

namespace App\Helpers;

class IndonesianCities
{
    private static ?array $cities = null;

    private static ?array $grouped = null;

    private static array $provinces = [
        11 => 'Aceh', 12 => 'Sumatera Utara', 13 => 'Sumatera Barat', 14 => 'Riau',
        15 => 'Jambi', 16 => 'Sumatera Selatan', 17 => 'Bengkulu', 18 => 'Lampung',
        19 => 'Kep. Bangka Belitung', 21 => 'Kep. Riau', 31 => 'DKI Jakarta',
        32 => 'Jawa Barat', 33 => 'Jawa Tengah', 34 => 'DI Yogyakarta', 35 => 'Jawa Timur',
        36 => 'Banten', 51 => 'Bali', 52 => 'Nusa Tenggara Barat', 53 => 'Nusa Tenggara Timur',
        61 => 'Kalimantan Barat', 62 => 'Kalimantan Tengah', 63 => 'Kalimantan Selatan',
        64 => 'Kalimantan Timur', 65 => 'Kalimantan Utara', 71 => 'Sulawesi Utara',
        72 => 'Sulawesi Tengah', 73 => 'Sulawesi Selatan', 74 => 'Sulawesi Tenggara',
        75 => 'Gorontalo', 76 => 'Sulawesi Barat', 81 => 'Maluku', 82 => 'Maluku Utara',
        91 => 'Papua', 92 => 'Papua Barat',
    ];

    public static function all(): array
    {
        if (self::$cities !== null) {
            return self::$cities;
        }

        $data = self::loadJson();
        self::$cities = array_map(fn ($c) => $c['name'], $data);

        return self::$cities;
    }

    public static function allWithProvinces(): array
    {
        if (self::$grouped !== null) {
            return self::$grouped;
        }

        $data = self::loadJson();
        $grouped = [];

        foreach ($data as $city) {
            $pid = $city['province_id'];
            $provinceName = self::$provinces[$pid] ?? 'Provinsi Lain';
            $grouped[$provinceName][] = $city['name'];
        }

        ksort($grouped);
        self::$grouped = $grouped;

        return self::$grouped;
    }

    public static function provinces(): array
    {
        return self::$provinces;
    }

    public static function search(string $query): array
    {
        $query = strtolower($query);

        return array_values(array_filter(
            self::all(),
            fn ($city) => str_contains(strtolower($city), $query)
        ));
    }

    private static function loadJson(): array
    {
        $path = database_path('data/indonesian-cities.json');

        if (! file_exists($path)) {
            return [];
        }

        return json_decode(file_get_contents($path), true) ?? [];
    }
}
