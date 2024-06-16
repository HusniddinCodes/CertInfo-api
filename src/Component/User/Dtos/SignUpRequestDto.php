<?php

declare(strict_types=1);

namespace App\Component\User\Dtos;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

readonly class SignUpRequestDto
{
    public function __construct(
        #[Assert\Email]
        #[Assert\NotBlank(message: 'Email cannot be blank.')]
        #[Groups(['user:write'])]
        private string $email,

        #[Assert\Length(min: 6, minMessage: 'Password must be at least {{ limit }} characters long')]
        #[Groups(['user:write'])]
        private string $password,

        #[Groups(['user:write'])]
        #[Assert\NotBlank(message: 'familyName cannot be blank.')]
        private string $familyName,

        #[Groups(['user:write'])]
        #[Assert\NotBlank(message: 'givenName cannot be blank.')]
        private string $givenName,
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getGivenName(): string
    {
        return $this->givenName;
    }

    public function getFamilyName(): string
    {
        return $this->familyName;
    }
}
