<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarLog extends Model
{
    protected $fillable = [
        'war_session_id',
        'peserta_kkn_id',
        'action',
        'meta',
    ];

    public function session()
    {
        return $this->belongsTo(WarSession::class);
    }

    public function pesertaKkn()
    {
        return $this->belongsTo(PesertaKkn::class);
    }
}
