<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles;

    public bool $skipVerificationEmail = false;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function mahasiswa()
    {
        return $this->hasOne(Mahasiswa::class);
    }

    public function verificationLogs()
    {
        return $this->hasMany(VerificationLog::class, 'admin_id');
    }

    public function verifiedPesertaKkn()
    {
        return $this->hasMany(PesertaKkn::class, 'verified_by');
    }

    public function verifiedDokumen()
    {
        return $this->hasMany(DokumenPendaftaran::class, 'verified_by');
    }

    public function dosenPembimbingLapangan()
    {
        return $this->hasOne(DosenPembimbingLapangan::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    public function isMahasiswa(): bool
    {
        return $this->hasRole('mahasiswa');
    }

    public function isPembimbing(): bool
    {
        return $this->hasRole('pembimbing');
    }

    public function hasCompletedBiodata(): bool
    {
        return $this->mahasiswa?->is_biodata_complete ?? false;
    }

    public function sendEmailVerificationNotification(): void
    {
        if ($this->skipVerificationEmail) {
            return;
        }

        parent::sendEmailVerificationNotification();
    }
}
