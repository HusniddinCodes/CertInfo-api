<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Component\User\Dtos\UserChangePasswordDto;
use App\Component\User\UserManager;
use App\Controller\Base\AbstractController;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class CreateUserController
 *
 * @method User findEntityOrError(ServiceEntityRepository $repository, int $id)
 *
 * @package App\Controller
 */
class UserChangePasswordAction extends AbstractController
{
    public function __invoke(
        User $user,
        UserManager $userManager,
        UserRepository $repository,
        UserChangePasswordDto $userChangePasswordDto,
        UserPasswordHasherInterface $userPasswordHasher ,
        int $id
    ): User {
        $this->validate($userChangePasswordDto);
        $oldPassword = $userChangePasswordDto->getOldPassword();

        if (!$userPasswordHasher->isPasswordValid($user, $oldPassword)) {
            throw new \Exception('Old password is incorrect!');
        }

        $user = $this->findEntityOrError($repository, $id);
        $this->validate($user);

        $userManager->hashPassword($user, $userChangePasswordDto->getNewPassword());
        $userManager->save($user, true);

        return $user;
    }
}
