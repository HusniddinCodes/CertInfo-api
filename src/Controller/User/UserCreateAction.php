<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Component\User\Dtos\SignUpRequestDto;
use App\Component\User\UserFactory;
use App\Component\User\UserManager;
use App\Component\User\UserWithPersonBuilder;
use App\Controller\Base\AbstractController;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;

class UserCreateAction extends AbstractController
{
    public function __invoke(
        UserFactory $userFactory,
        UserManager $userManager,
        Request $request,
        UserWithPersonBuilder $userMaker,
        SignUpRequestDto $signUpRequestDto,
    ): User {
        $this->validate($signUpRequestDto);

        $user = $userMaker->make(
            $signUpRequestDto->getEmail(),
            $signUpRequestDto->getPassword(),
            $signUpRequestDto->getFamilyName(),
            $signUpRequestDto->getGivenName(),
            null
        );

        $userManager->save($user, true);

        return $user;
    }
}
