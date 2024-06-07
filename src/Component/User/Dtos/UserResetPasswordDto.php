<?php

declare(strict_types=1);

namespace App\Component\User\Dtos;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

readonly class UserResetPasswordDto
{
    public function __construct(
        #[Groups(['user:resetPassword:write'])]
        private string $secretKey,

        #[Assert\Length(min: 6, minMessage: 'Password must be at least {{ limit }} characters long')]
        #[Groups(['user:resetPassword:write'])]
        private string $newPassword)
    {
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    public function getNewPassword(): string
    {
        return $this->newPassword;
    }
}
