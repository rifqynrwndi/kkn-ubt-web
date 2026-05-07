<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesaGelombang extends Model
    {
        protected $table = 'desa_gelombang';

        protected $fillable = [
            'gelombang_id',
            'desa_id',
            'kuota_total',
            'status',
            'dosen_pembimbing_lapangan_id',
        ];

        public function desa()
        {
            return $this->belongsTo(Desa::class);
        }

        public function gelombang()
        {
            return $this->belongsTo(Gelombang::class);
        }

        public function dpl()
        {
            return $this->belongsTo(
                DosenPembimbingLapangan::class,
                'dosen_pembimbing_lapangan_id'
            );
        }

        public function kuotaFakultas()
        {
            return $this->hasMany(KuotaFakultasDesa::class);
        }
}
