<?php

declare(strict_types=1);

namespace App\Message;

class GreetingNewCertificateByEmail
{
    public function __construct(
        private string $email,
        private string $url,
        private string $download,
        private string $image,
        private string $familyName,
        private string $givenName,
    ) {
    }
    public function getEmail(): string
    {
        return $this->email;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getDownload(): string
    {
        return $this->download;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getFamilyName(): string
    {
        return $this->familyName;
    }

    public function getGivenName(): string
    {
        return $this->givenName;
    }
}
