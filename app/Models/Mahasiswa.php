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
        'foto',
        'prodi_id',
        'nama_ortu',
        'no_hp_ortu',
        'alamat_ortu',
        'is_biodata_complete',
    ];

    protected $casts = [
        'is_biodata_complete' => 'boolean',
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

    public function getNameAttribute(): ?string
    {
        return $this->user?->name;
    }
}
