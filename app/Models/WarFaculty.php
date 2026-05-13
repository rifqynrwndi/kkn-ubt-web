<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarFaculty extends Model
{
    protected $fillable = [
        'war_session_id',
        'fakultas_id',
        'quota',
        'filled',
        'start_at',
        'end_at',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
    ];

    public function warSession()
    {
        return $this->belongsTo(WarSession::class);
    }

    public function fakultas()
    {
        return $this->belongsTo(Fakultas::class);
    }

    public function getSisaAttribute(): int
    {
        return $this->quota - $this->filled;
    }

    public function getIsActiveAttribute(): bool
    {
        if (! $this->start_at || ! $this->end_at) {
            return false;
        }

        return now()->between($this->start_at, $this->end_at);
    }

    public function getStatusJadwalAttribute(): string
    {
        if (! $this->start_at) {
            return 'belum dijadwalkan';
        }

        if (now()->lt($this->start_at)) {
            return 'belum mulai';
        }

        if (now()->gt($this->end_at)) {
            return 'selesai';
        }

        return 'aktif';
    }
}
