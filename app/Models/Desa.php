<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Desa extends Model
    {
        protected $table = 'desa';

        protected $fillable = [
            'kecamatan_id',
            'nama_desa',
            'aktif',
        ];

        public function kecamatan()
        {
            return $this->belongsTo(Kecamatan::class);
        }

        public function desaGelombang()
        {
            return $this->hasMany(DesaGelombang::class);
        }
    }
