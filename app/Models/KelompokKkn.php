<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KelompokKkn extends Model
{
    protected $table = 'kelompok_kkn';

    protected $fillable = [
        'desa_gelombang_id',
        'dosen_pembimbing_lapangan_id',
        'nama_kelompok',
        'kuota',
        'status',
    ];

    public function desaGelombang()
    {
        return $this->belongsTo(DesaGelombang::class);
    }

    public function dosenPembimbingLapangan()
    {
        return $this->belongsTo(DosenPembimbingLapangan::class);
    }

    public function pesertaKkn()
    {
        return $this->hasMany(PesertaKkn::class);
    }

    public function getTerisiAttribute()
    {
        return $this->pesertaKkn()->count();
    }

    public function getSisaKuotaAttribute()
    {
        return $this->kuota - $this->terisi;
    }

    public function getIsFullAttribute()
    {
        return $this->terisi >= $this->kuota;
    }

    protected static function booted()
    {
        static::creating(function ($kelompok) {

            $words = [
                'PAPAYA',
                'MANGGA',
                'DURIAN',
                'RAMBUTAN',
                'NANGKA',
                'KELAPA',
                'SUKUN',
                'ALPUKAT',
                'MELON',
                'SEMANGKA',
            ];

            do {

                $randomWord = $words[array_rand($words)];

                $kode = $randomWord . now()->timestamp . rand(10, 99);

            } while (
                self::where('kode_kelompok', $kode)->exists()
            );

            $kelompok->kode_kelompok = $kode;
        });

        static::updated(function ($kelompok) {

            $jumlah = $kelompok->pesertaKkn()->count();

            if ($jumlah >= $kelompok->kuota) {

                $kelompok->updateQuietly([
                    'status' => 'penuh'
                ]);

            }

        });
    }
}
