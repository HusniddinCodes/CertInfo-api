<?php

namespace App\Message;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;


#[AsMessageHandler]
class GreetingNewCertificateByEmailHandler
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function __invoke(GreetingNewCertificateByEmail $message): void
    {
        $email = (new TemplatedEmail())
            ->from('change@me.uz')
            ->to($message->getEmail())
            ->subject('Hurmatli ' . $message->getFamilyName() . ' ' . $message->getGivenName() . ', sizga Kadirov akademiyasi tomonidan sertifikat taqdim etildi!')
            ->htmlTemplate('send-certificate.html.twig')
            ->context([
                'url' => $message->getUrl(),
                'download' => $message->getDownload(),
                'img' => $message->getImage(),
            ]);

        $this->mailer->send($email);
    }
}
