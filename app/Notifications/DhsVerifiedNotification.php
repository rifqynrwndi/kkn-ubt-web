<?php

namespace App\Notifications;

use App\Models\Mahasiswa;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DhsVerifiedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Mahasiswa $mahasiswa,
        public string $status
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $statusText = $this->status === 'verified' ? 'diverifikasi' : 'ditolak';
        $message = "DHS Anda telah {$statusText}.";

        if ($this->status === 'rejected' && $this->mahasiswa->dhs_catatan) {
            $message .= " Catatan: {$this->mahasiswa->dhs_catatan}";
        }

        return [
            'type' => 'dhs_verified',
            'status' => $this->status,
            'message' => $message,
            'url' => '/biodata/edit',
        ];
    }
}
