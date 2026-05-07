<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KuotaFakultasDesa extends Model
{
    protected $table = 'kuota_fakultas_desa';

    protected $fillable = [
        'desa_gelombang_id',
        'fakultas_id',
        'kuota',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function desaGelombang()
    {
        return $this->belongsTo(DesaGelombang::class);
    }

    public function fakultas()
    {
        return $this->belongsTo(Fakultas::class);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER
    |--------------------------------------------------------------------------
    */

    public function getTerisiAttribute(): int
    {
        return KelompokMember::whereHas('pesertaKkn.mahasiswa', function ($query) {
            $query->where('fakultas_id', $this->fakultas_id);
        })
        ->whereHas('kelompok', function ($query) {
            $query->where('desa_gelombang_id', $this->desa_gelombang_id);
        })
        ->count();
    }

    public function getSisaAttribute(): int
    {
        return max(0, $this->kuota - $this->terisi);
    }

    public function getIsFullAttribute(): bool
    {
        return $this->sisa <= 0;
    }
}
