<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KelompokKuota extends Model
{
    protected $table = 'kelompok_kuota';

    protected $fillable = [
        'kelompok_kkn_id',
        'fakultas_id',
        'kuota',
        'kuota_laki',
        'kuota_perempuan',
    ];

    public function kelompokKkn()
    {
        return $this->belongsTo(KelompokKkn::class);
    }

    public function fakultas()
    {
        return $this->belongsTo(Fakultas::class);
    }

    public function getTotalKuotaAttribute(): int
    {
        return $this->kuota_laki + $this->kuota_perempuan;
    }
}
