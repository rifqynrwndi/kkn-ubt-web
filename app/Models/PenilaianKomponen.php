<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PenilaianKomponen extends Model
{
    protected $table = 'penilaian_komponen';
    protected $fillable = ['nama_komponen','deskripsi','kategori','bobot','urutan','is_active'];
    protected $casts = ['is_active'=>'boolean'];
}
