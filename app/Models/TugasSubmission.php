<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TugasSubmission extends Model
{
    protected $table = 'tugas_submission';
    protected $fillable = ['tugas_kelompok_id','peserta_kkn_id','judul','deskripsi','file_path','file_name','file_size','status','komentar_dpl','reviewed_by','reviewed_at'];
    protected $casts = ['reviewed_at'=>'datetime'];
    public function tugasKelompok() { return $this->belongsTo(TugasKelompok::class); }
    public function pesertaKkn() { return $this->belongsTo(PesertaKkn::class); }
    public function reviewedBy() { return $this->belongsTo(User::class, 'reviewed_by'); }
}
