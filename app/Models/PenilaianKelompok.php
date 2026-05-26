<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PenilaianKelompok extends Model
{
    protected $table = 'penilaian_kelompok';
    protected $fillable = ['kelompok_kkn_id','komponen_id','nilai','input_by','input_at'];
    protected $casts = ['nilai'=>'decimal:2','input_at'=>'datetime'];
    public function komponen() { return $this->belongsTo(PenilaianKomponen::class, 'komponen_id'); }
    public function inputBy() { return $this->belongsTo(User::class, 'input_by'); }
}
