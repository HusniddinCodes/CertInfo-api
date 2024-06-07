<?php

namespace App\Message;

readonly class GreetingResetPasswordByEmail
{
    public function __construct(
        private string $email,
        private string $token,
        private string $url
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
