<?php

namespace App\Notifications;

use App\Models\Mahasiswa;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class DhsUploadedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Mahasiswa $mahasiswa
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'dhs_uploaded',
            'mahasiswa_name' => $this->mahasiswa->user->name,
            'mahasiswa_npm' => $this->mahasiswa->npm,
            'message' => "{$this->mahasiswa->user->name} ({$this->mahasiswa->npm}) mengunggah DHS.",
            'url' => "/mahasiswa/{$this->mahasiswa->user_id}",
        ];
    }
}
