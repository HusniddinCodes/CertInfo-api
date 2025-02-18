<?php

declare(strict_types=1);

namespace App\Component\User\Dtos;

use App\Entity\MediaObject;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

readonly class UserChangeDataDto
{
    public function __construct(
        #[Assert\Email]
        #[Assert\NotBlank(message: 'Email cannot be blank.')]
        #[Groups(['user:put:write'])]
        private string $email,

        #[Groups(['user:put:write'])]
        #[Assert\NotBlank(message: 'familyName cannot be blank.')]
        private string $familyName,

        #[Assert\NotBlank(message: 'givenName cannot be blank.')]
        #[Groups(['user:put:write'])]
        private string $givenName,

        #[Groups(['user:put:write'])]
        private ?MediaObject $avatar = null
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFamilyName(): string
    {
        return $this->familyName;
    }

    public function getGivenName(): string
    {
        return $this->givenName;
    }

    public function getAvatar(): ?MediaObject
    {
        return $this->avatar;
    }

}
