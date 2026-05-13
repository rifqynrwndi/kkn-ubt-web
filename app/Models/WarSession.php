<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarSession extends Model
{
    protected $fillable = [
        'name',
        'gelombang_id',
        'start_at',
        'end_at',
        'status',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION GEL0MBANG
    |--------------------------------------------------------------------------
    */
    public function gelombang()
    {
        return $this->belongsTo(
            Gelombang::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RELATION FAKULTAS WAR
    |--------------------------------------------------------------------------
    */
    public function faculties()
    {
        return $this->hasMany(
            WarFaculty::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PARTICIPANTS
    |--------------------------------------------------------------------------
    */
    public function participants()
    {
        return $this->hasMany(
            WarParticipant::class
        );
    }
}
