<?php

declare(strict_types=1);

namespace App\Message;

readonly class GreetingResetPasswordByEmail
{
    public function __construct(
        private string $email,
        private string $secretKey,
        private string $url
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
