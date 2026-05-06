<?php

namespace App\Notifications;

use App\Models\DokumenPendaftaran;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DokumenVerifiedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public DokumenPendaftaran $dokumen
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Dokumen Diverifikasi',
            'message' => 'Dokumen ' . $this->dokumen->jenis_dokumen_label .
                ' Anda telah di-' . $this->dokumen->status_verifikasi . '.',
            'dokumen_id' => $this->dokumen->id,
            'type' => 'dokumen_verified',
        ];
    }
}
