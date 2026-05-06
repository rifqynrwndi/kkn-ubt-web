<?php

namespace App\Notifications;

use App\Models\PesertaKkn;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DokumenUploadedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public PesertaKkn $peserta
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Dokumen Baru Diunggah',
            'message' => $this->peserta->mahasiswa->user->name .
                ' telah mengunggah dokumen pendaftaran KKN.',
            'peserta_kkn_id' => $this->peserta->id,
            'type' => 'dokumen_uploaded',
        ];
    }
}
