<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gelombang extends Model
{
    protected $table = 'gelombang';

    protected $fillable = [
        'nama_gelombang',
        'tahun',
        'tgl_mulai',
        'tgl_akhir',
        'kuota_laki',
        'kuota_perempuan',
        'kuota_total',
        'status',
        'skip_dokumen',
        'required_documents',
    ];

    protected $casts = [
        'skip_dokumen' => 'boolean',
        'required_documents' => 'array',
    ];

    public function getSkipDokumenAttribute(): bool
    {
        return (bool) $this->attributes['skip_dokumen'];
    }

    public function getRequiredDocumentTypesAttribute(): array
    {
        $value = $this->attributes['required_documents'];

        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        return $value ?? array_keys(DokumenPendaftaran::getDocumentLabels());
    }

    public function isDocumentRequired(string $jenisDokumen): bool
    {
        return in_array($jenisDokumen, $this->getRequiredDocumentTypesAttribute());
    }

    public function pesertaKkn()
    {
        return $this->hasMany(PesertaKkn::class);
    }

    public function warSessions()
    {
        return $this->hasMany(WarSession::class);
    }
}
