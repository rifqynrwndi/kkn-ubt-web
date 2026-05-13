<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DokumenPendaftaran extends Model
{
    protected $table = 'dokumen_pendaftaran';

    protected $fillable = [
        'peserta_kkn_id',
        'file_id',
        'jenis_dokumen',
        'status_verifikasi',
        'catatan_revisi',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function pesertaKkn()
    {
        return $this->belongsTo(PesertaKkn::class);
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public static function getDocumentLabels(): array
    {
        return [
            'dhs' => 'Daftar Hasil Studi (DHS)',
            'surat_pernyataan' => 'Surat Pernyataan KKN',
            'surat_ortu' => 'Surat Keterangan Orang Tua',
            'surat_vaksin' => 'Surat Keterangan Vaksin',
            'surat_dokter' => 'Surat Keterangan Dokter',
        ];
    }

    public function getJenisDokumenLabelAttribute(): string
    {
        return self::getDocumentLabels()[$this->jenis_dokumen]
            ?? $this->jenis_dokumen;
    }

    public const REQUIRED_DOCUMENTS = [
        'dhs',
        'surat_pernyataan',
        'surat_ortu',
        'surat_vaksin',
        'surat_dokter',
    ];
}
