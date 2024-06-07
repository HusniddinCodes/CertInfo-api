<?php

declare(strict_types=1);

namespace App\Component\SecretKey;

use App\Entity\SecretKey;
use App\Entity\User;

readonly class SecretKeyFactory
{
    public function create(User $user): SecretKey
    {
        return (new SecretKey())
            ->setSecretKey($this->generateToken())
            ->setUser($user);
    }

    private function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
