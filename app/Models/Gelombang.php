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
    ];

    public function pesertaKkn()
    {
        return $this->hasMany(PesertaKkn::class);
    }
}


