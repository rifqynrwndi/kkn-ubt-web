<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WarSession;
use App\Models\PesertaKkn;
use App\Models\KelompokKkn;

class WarParticipant extends Model
{
    protected $fillable = [
        'war_session_id',
        'peserta_kkn_id',
        'kelompok_kkn_id',
        'status',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(WarSession::class, 'war_session_id');
    }

    public function pesertaKkn()
    {
        return $this->belongsTo(PesertaKkn::class, 'peserta_kkn_id');
    }

    public function kelompokKkn()
    {
        return $this->belongsTo(KelompokKkn::class, 'kelompok_kkn_id');
    }
}
