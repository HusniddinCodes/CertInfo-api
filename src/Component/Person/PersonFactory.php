<?php

declare(strict_types=1);

namespace App\Component\Person;

use App\Entity\MediaObject;
use App\Entity\Person;
use App\Entity\User;

class PersonFactory
{
    public function create(
        User $user,
        string $familyName,
        string $givenName,
        ?MediaObject $avatar = null,
    ): Person {
        return (new Person())
            ->setUser($user)
            ->setFamilyName($familyName)
            ->setGivenName($givenName)
            ->setAvatar($avatar);
    }
}
