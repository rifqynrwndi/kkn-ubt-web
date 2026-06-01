<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PenilaianIndividu extends Model
{
    protected $table = 'penilaian_individu';
    protected $fillable = ['kelompok_kkn_id','peserta_kkn_id','komponen_id','nilai','input_by'];
    protected $casts = ['nilai'=>'decimal:2'];
    public function komponen() { return $this->belongsTo(PenilaianKomponen::class, 'komponen_id'); }
    public function pesertaKkn() { return $this->belongsTo(PesertaKkn::class); }
    public function kelompokKkn() { return $this->belongsTo(KelompokKkn::class); }
    public function inputBy() { return $this->belongsTo(User::class, 'input_by'); }
}
