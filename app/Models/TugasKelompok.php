<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TugasKelompok extends Model
{
    protected $table = 'tugas_kelompok';
    protected $fillable = ['kelompok_kkn_id','kategori','nama_tugas','deskripsi','periode_mulai','periode_akhir','is_active','is_wajib','created_by'];
    protected $casts = ['periode_mulai'=>'date','periode_akhir'=>'date','is_active'=>'boolean','is_wajib'=>'boolean'];
    public function submissions() { return $this->hasMany(TugasSubmission::class); }
    public function kelompokKkn() { return $this->belongsTo(KelompokKkn::class); }
}
