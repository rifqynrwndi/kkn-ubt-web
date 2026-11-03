<?php

namespace App\Support;

final class TugasTemplate
{
    public const DRAFT_ARTIKEL = 'Draft Artikel';

    public const WAJIB = [
        'Program Kerja',
        'Video Profil Desa',
        'Draft Artikel',
        'Laporan Program KKN',
    ];

    public static function isBuiltIn(string $nama): bool
    {
        return in_array($nama, self::WAJIB, true);
    }
}
