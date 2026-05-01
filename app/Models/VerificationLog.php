<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerificationLog extends Model
{
    protected $table = 'verification_logs';

    protected $fillable = [
        'peserta_kkn_id',
        'admin_id',
        'action',
        'notes',
    ];

    public function pesertaKkn()
    {
        return $this->belongsTo(PesertaKkn::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
