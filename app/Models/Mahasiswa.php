<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    protected $table = 'mahasiswa';

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'npm',
        'jenis_kelamin',
        'no_hp',
        'birth_place',
        'birth_date',
        'foto',
        'prodi_id',
        'nama_ortu',
        'no_hp_ortu',
        'alamat_ortu',
        'is_biodata_complete',
        'dhs_path',
        'dhs_status',
        'dhs_verified_by',
        'dhs_verified_at',
        'dhs_catatan',
    ];

    protected $casts = [
        'is_biodata_complete' => 'boolean',
        'birth_date' => 'date',
        'dhs_verified_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function prodi()
    {
        return $this->belongsTo(ProgramStudi::class, 'prodi_id');
    }

    public function pesertaKkn()
    {
        return $this->hasMany(PesertaKkn::class, 'mahasiswa_id', 'user_id');
    }

    public function dhsVerifier()
    {
        return $this->belongsTo(User::class, 'dhs_verified_by');
    }

    public function hasDhsVerified(): bool
    {
        return $this->dhs_status === 'verified';
    }

    public function getNameAttribute(): ?string
    {
        return $this->user?->name;
    }
}
