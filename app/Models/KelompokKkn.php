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
        'ketua_peserta_id',
        'foto_kelompok',
        'status_tahap',
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

    public function ketua()
    {
        return $this->belongsTo(
            PesertaKkn::class,
            'ketua_peserta_id'
        );
    }

    public function generateKetua()
    {
        if ($this->ketua_peserta_id) {
            return;
        }

        $randomKetua = $this->pesertaKkn()
            ->inRandomOrder()
            ->first();

        if ($randomKetua) {

            $this->updateQuietly([
                'ketua_peserta_id' => $randomKetua->id
            ]);

        }
    }

    public function tugasKelompok()
    {
        return $this->hasMany(TugasKelompok::class);
    }

    public function kuotaFakultas()
    {
        return $this->hasMany(KelompokKuota::class);
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

        // Status 'penuh' is handled explicitly in tambahAnggota() and WarAllocationService
    }
}
