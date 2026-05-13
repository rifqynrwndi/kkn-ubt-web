<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gelombang extends Model
{
    protected $table = 'gelombang';

    protected $fillable = [
        'nama_gelombang',
        'tahun',
        'tgl_mulai',
        'tgl_akhir',
        'kuota_laki',
        'kuota_perempuan',
        'kuota_total',
        'status',
        'skip_dokumen',
    ];

    protected $casts = [
        'skip_dokumen' => 'boolean',
    ];

    public function getSkipDokumenAttribute(): bool
    {
        return (bool) $this->attributes['skip_dokumen'];
    }

    public function pesertaKkn()
    {
        return $this->hasMany(PesertaKkn::class);
    }
}


