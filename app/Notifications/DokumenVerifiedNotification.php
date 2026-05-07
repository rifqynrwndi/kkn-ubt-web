<?php

namespace App\Notifications;

use App\Models\PesertaKkn;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DokumenVerifiedNotification extends Notification
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
        $verified = [];
        $revision = [];
        $rejected = [];

        foreach ($this->peserta->dokumenPendaftaran as $dokumen) {

            switch ($dokumen->status_verifikasi) {

                case 'verified':
                    $verified[] = $dokumen->jenis_dokumen_label;
                    break;

                case 'revision_required':
                    $revision[] = $dokumen->jenis_dokumen_label;
                    break;

                case 'rejected':
                    $rejected[] = $dokumen->jenis_dokumen_label;
                    break;
            }
        }

        return [
            'title' => 'Status Verifikasi Dokumen Diperbarui',

            'message' => $this->buildMessage(
                $verified,
                $revision,
                $rejected
            ),

            'peserta_kkn_id' => $this->peserta->id,

            'type' => 'dokumen_verified',
        ];
    }

    private function buildMessage($verified, $revision, $rejected): string
    {
        $messages = [];

        if (!empty($verified)) {
            $messages[] =
                'Dokumen berikut telah diverifikasi: ' .
                implode(', ', $verified) . '.';
        }

        if (!empty($revision)) {
            $messages[] =
                'Dokumen berikut perlu revisi: ' .
                implode(', ', $revision) . '.';
        }

        if (!empty($rejected)) {
            $messages[] =
                'Dokumen berikut ditolak: ' .
                implode(', ', $rejected) . '.';
        }

        return implode(' ', $messages);
    }
}
