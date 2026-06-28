<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LaporanDpl extends Model
{
    protected $table = 'laporan_dpl';
    protected $fillable = ['kelompok_kkn_id','dpl_id','jenis','judul','deskripsi','file_path','file_name'];
    public function kelompokKkn() { return $this->belongsTo(KelompokKkn::class); }
    public function dpl() { return $this->belongsTo(DosenPembimbingLapangan::class, 'dpl_id'); }
}
