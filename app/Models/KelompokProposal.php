<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KelompokProposal extends Model
{
    protected $table = 'kelompok_proposal';

    protected $fillable = [
        'kelompok_kkn_id', 'pendahuluan', 'tujuan', 'manfaat',
        'hasil_observasi', 'rancangan_program', 'solusi_ide',
        'status', 'submitted_by', 'submitted_at',
        'komentar_dpl', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function kelompokKkn() { return $this->belongsTo(KelompokKkn::class); }
    public function submittedBy() { return $this->belongsTo(PesertaKkn::class, 'submitted_by'); }
    public function reviewedBy() { return $this->belongsTo(User::class, 'reviewed_by'); }
}
