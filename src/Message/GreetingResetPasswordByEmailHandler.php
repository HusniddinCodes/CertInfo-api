<?php

declare(strict_types=1);

namespace App\Message;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GreetingResetPasswordByEmailHandler
{
    public function __construct(private readonly MailerInterface $mailer, private readonly ParameterBagInterface $params) {
    }

    public function __invoke(GreetingResetPasswordByEmail $message): void
    {
        $email = (new TemplatedEmail())
            //todo  'mailer uchun email adresni o'zgartirish kerak'
            ->from($this->params->get('mailer_from_address'))
            ->to($message->getEmail())
            ->subject('Parolni yangilash')
            ->htmlTemplate('resetPassword.html.twig')
            ->context([
                'secretKey' => $message->getSecretKey(),
                'url' => $message->getUrl(),
            ]);

        $this->mailer->send($email);
    }
}
