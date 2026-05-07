<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DosenPembimbingLapangan extends Model
{
    protected $table = 'dosen_pembimbing_lapangan';

    protected $fillable = [
        'user_id',
        'nidn',
        'fakultas_id',
        'no_hp',
        'jenis_kelamin',
        'alamat',
        'status',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fakultas()
    {
        return $this->belongsTo(Fakultas::class);
    }

    public function kelompokKkn()
    {
        return $this->hasMany(KelompokKkn::class, 'dosen_pembimbing_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getNamaAttribute(): ?string
    {
        return $this->user?->name;
    }
}
