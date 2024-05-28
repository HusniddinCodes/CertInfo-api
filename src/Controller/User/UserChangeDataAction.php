<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Component\Person\PersonManager;
use App\Component\User\Dtos\UserChangeDataDto;
use App\Component\User\UserManager;
use App\Controller\Base\AbstractController;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class UserChangeDataAction extends AbstractController
{
    public function __invoke(
        User $user,
        UserRepository $userRepository,
        UserManager $userManager,
        UserChangeDataDto $userChangeDataDto,
        PersonManager $personManager,
    ): User {
        $previousEmail = $user->getEmail();
        $userEmail = $userRepository->findOneByEmail($userChangeDataDto->getEmail());

        if ($userEmail instanceof User && $userChangeDataDto->getEmail() !== $previousEmail) {
            throw new UnprocessableEntityHttpException('Bunday email tizmda mavjud');
        }

        $user->setEmail($userChangeDataDto->getEmail());
        $userManager->save($user);

        $person = $user->getPerson();

        $person
            ->setFamilyName($userChangeDataDto->getFamilyName())
            ->setGivenName($userChangeDataDto->getGivenName())
            ->setAvatar($userChangeDataDto->getAvatar())
            ->setUpdatedBy($this->getUser());

        $personManager->save($person, true);

        return $user;
    }
}
