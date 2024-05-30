<?php

declare(strict_types=1);


namespace App\Component\User;

use App\Component\Person\PersonFactory;
use App\Component\Person\PersonManager;
use App\Entity\MediaObject;
use App\Entity\User;

readonly class UserWithPersonBuilder
{
    public function __construct(
        private UserFactory $userFactory,
        private UserManager $userManager,
        private PersonFactory $personFactory,
        private PersonManager $personManager,
    ) {
    }

    public function make(string $email, string $password, string $familyName, string $givenName, ?MediaObject $avatar): User
    {
        $user = $this->userFactory->create($email, $password);
        $person = $this->personFactory->create($user, $familyName, $givenName, $avatar);
        $this->userManager->save($user);
        $this->personManager->save($person);

        return $user;
    }
}
