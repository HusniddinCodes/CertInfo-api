<?php

declare(strict_types=1);


namespace App\Component\User;

use App\Component\Person\PersonFactory;
use App\Component\Person\PersonManager;
use App\Entity\MediaObject;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

readonly class UserWithPersonBuilder
{
    public function __construct(
        private UserFactory $userFactory,
        private UserManager $userManager,
        private PersonFactory $personFactory,
        private PersonManager $personManager,
        private UserRepository $userRepository,
    ) {
    }

    public function make(string $email, string $password, string $familyName, string $givenName, ?MediaObject $avatar): User
    {
        $user = $this->userRepository->findOneByEmail($email);

        if ($user instanceof User) {
            throw new UnprocessableEntityHttpException('Such an email is available in the system');
        }

        $user = $this->userFactory->create($email, $password);
        $person = $this->personFactory->create($user, $familyName, $givenName, $avatar);
        $this->userManager->save($user);
        $this->personManager->save($person);

        return $user;
    }
}
