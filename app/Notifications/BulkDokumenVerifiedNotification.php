<?php

namespace App\Notifications;

use App\Models\PesertaKkn;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BulkDokumenVerifiedNotification extends Notification
{
    use Queueable;

    protected $peserta;

    public function __construct(PesertaKkn $peserta)
    {
        $this->peserta = $peserta;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Verifikasi Dokumen Diperbarui',
            'message' =>
                'Status verifikasi dokumen KKN Anda telah diperbarui oleh admin.',
            'peserta_kkn_id' => $this->peserta->id,
            'type' => 'bulk_verification',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verifikasi Dokumen Diperbarui')
            ->line('Status verifikasi dokumen KKN Anda telah diperbarui.');
    }
}
