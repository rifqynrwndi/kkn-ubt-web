<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LogBook extends Model
{
    protected $table = 'log_book';
    protected $fillable = ['peserta_kkn_id','kelompok_kkn_id','tanggal','judul','deskripsi','file_path','file_name','status','komentar_dpl','is_validated','validated_by','validated_at'];
    protected $casts = ['tanggal'=>'date','is_validated'=>'boolean','validated_at'=>'datetime'];

    public function pesertaKkn() { return $this->belongsTo(PesertaKkn::class); }
    public function validatedBy() { return $this->belongsTo(User::class,'validated_by'); }
}
