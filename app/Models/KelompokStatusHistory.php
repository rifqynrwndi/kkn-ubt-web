<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KelompokStatusHistory extends Model
{
    protected $table = 'kelompok_status_history';
    protected $fillable = ['kelompok_kkn_id','status_lama','status_baru','keterangan','changed_by','changed_by_role'];

    public function kelompokKkn() { return $this->belongsTo(KelompokKkn::class); }
    public function changedBy() { return $this->belongsTo(User::class, 'changed_by'); }
}
