<?php

declare(strict_types=1);

namespace App\Message;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;


#[AsMessageHandler]
class GreetingNewCertificateByEmailHandler
{

    public function __construct(private readonly MailerInterface $mailer, private readonly ParameterBagInterface $params)
    {
    }

    public function __invoke(GreetingNewCertificateByEmail $message): void
    {
        $email = (new TemplatedEmail())
            //todo  'mailer uchun email adresni o'zgartirish kerak'
            ->from($this->params->get('mailer_from_address'))
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
