<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;

class CustomVerifyEmail extends VerifyEmail
{
    use Queueable;

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return $this->buildMailMessage()
            ->subject('Verifikasi Email — KKN Universitas Borneo Tarakan')
            ->greeting('Halo!')
            ->line('Terima kasih telah mendaftar di Sistem Informasi KKN Universitas Borneo Tarakan.')
            ->line('Silakan klik tombol di bawah ini untuk memverifikasi alamat email Anda.')
            ->action('Verifikasi Email', $verificationUrl)
            ->line('Jika Anda tidak merasa mendaftar akun ini, abaikan email ini.')
            ->salutation('Salam hormat,')
            ->line('LPPM Universitas Borneo Tarakan')
            ->line('Jalan Amal Lama No. 1, Kota Tarakan')
            ->line('Kalimantan Utara, Indonesia 77115');
    }
}
