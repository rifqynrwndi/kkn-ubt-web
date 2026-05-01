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
        'status',
    ];

    protected $casts = [
        'tgl_mulai' => 'date',
        'tgl_akhir' => 'date',
    ];

    public function pesertaKkn()
    {
        return $this->hasMany(PesertaKkn::class);
    }
}
