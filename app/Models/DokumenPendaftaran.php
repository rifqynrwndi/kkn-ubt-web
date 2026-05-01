<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DokumenPendaftaran extends Model
{
    protected $table = 'dokumen_pendaftaran';

    protected $fillable = [
        'peserta_kkn_id',
        'file_id',
        'jenis_dokumen',
        'status_verifikasi',
        'catatan_revisi',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function pesertaKkn()
    {
        return $this->belongsTo(PesertaKkn::class);
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
