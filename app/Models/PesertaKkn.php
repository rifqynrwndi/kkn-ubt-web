<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesertaKkn extends Model
{
    protected $table = 'peserta_kkn';

    protected $fillable = [
        'mahasiswa_id',
        'gelombang_id',
        'kelompok_kkn_id',
        'status_pendaftaran',
        'submitted_at',
        'verified_by',
        'verified_at',
        'catatan_admin',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id', 'user_id');
    }

    public function gelombang()
    {
        return $this->belongsTo(Gelombang::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function dokumenPendaftaran()
    {
        return $this->hasMany(DokumenPendaftaran::class);
    }

    public function verificationLogs()
    {
        return $this->hasMany(VerificationLog::class);
    }

    public function kelompokKkn()
    {
        return $this->belongsTo(KelompokKkn::class);
    }

    public function warLogs()
    {
        return $this->hasMany(WarLog::class);
    }

    public function getNameAttribute(): ?string
    {
        return $this->mahasiswa?->user?->name;
    }
}
