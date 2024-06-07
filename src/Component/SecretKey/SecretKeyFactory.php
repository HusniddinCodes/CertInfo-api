<?php

declare(strict_types=1);

namespace App\Component\SecretKey;

use App\Entity\SecretKey;
use App\Entity\User;

readonly class SecretKeyFactory
{
    public function __construct(private GenerateSecurityKey $generateSecurityKey)
    {
    }

    public function create(User $user): SecretKey
    {
        return (new SecretKey())
            ->setSecretKey($this->generateSecurityKey->generate())
            ->setUser($user);
    }
}
