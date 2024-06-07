<?php

namespace App\Message;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GreetingResetPasswordByEmailHandler
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function __invoke(GreetingResetPasswordByEmail $message): void
    {
        $email = (new TemplatedEmail())
            //todo  'mailer uchun email adresni o'zgartirish kerak'
            ->from('change@me.uz')
            ->to($message->getEmail())
            ->subject('Parolni yangilash')
            ->htmlTemplate('resetPassword.html.twig')
            ->context([
                'token' => $message->getToken(),
                'url' => $message->getUrl(),
            ]);

        $this->mailer->send($email);
    }
}
