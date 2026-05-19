<?php

namespace App\Mail;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class ResendTransport extends AbstractTransport
{
    public function __construct(
        private readonly string $apiKey,
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $from = $email->getFrom()[0];

        Http::withToken($this->apiKey)
            ->acceptJson()
            ->post('https://api.resend.com/emails', [
                'from'    => config('mail.from.name') . ' <' . config('mail.from.address') . '>',
                'to'      => array_map(fn($a) => $a->getAddress(), $email->getTo()),
                'subject' => $email->getSubject(),
                'html'    => $email->getHtmlBody() ?? $email->getTextBody(),
            ]);
    }

    public function __toString(): string
    {
        return 'resend';
    }
}
