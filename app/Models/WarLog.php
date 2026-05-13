<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WarSession;
use App\Models\PesertaKkn;

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
