<?php

declare(strict_types=1);

namespace App\Controller\User;


use App\Component\SecretKey\CheckIsExpired;
use App\Component\User\Dtos\UserResetPasswordDto;
use App\Component\User\TokensCreator;
use App\Component\User\UserManager;
use App\Repository\SecretKeyRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserResetPasswordAction extends AbstractController
{
    public function __invoke(
        UserRepository $userRepository,
        SecretKeyRepository $secretKeyRepository,
        CheckIsExpired $checkIsExpired,
        TokensCreator $secretKeysCreator,
        UserPasswordHasherInterface $passwordHasher,
        UserManager $userManager,
        UserResetPasswordDto $userResetPasswordDto
    ): JsonResponse {
        $secretKeyString = $userResetPasswordDto->getSecretKey() ?? null;
        $newPassword = $userResetPasswordDto->getNewPassword() ?? null;

        if (null === $secretKeyString || null === $newPassword) {
            throw new HttpException(400, 'Error');
        }

        if (strlen($newPassword) < 6) {
            throw new HttpException(400, "Password must contain at least 6 characters");
        }

        $secretKey = $secretKeyRepository->findOneBy(['secretKey' => $secretKeyString]);
        $checkIsExpired->isExpiredResetPasswordSecretKey($secretKey, $secretKeyRepository);
        $secretKeysDto = $secretKeysCreator->create($secretKey->getUser());

        $user = $secretKey->getUser();
        $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
        $userManager->save($user, true);

        $secretKeyRepository->remove($secretKey, true);

        return new JsonResponse(
            ['accessToken' => $secretKeysDto->getAccessToken(), 'refreshToken' => $secretKeysDto->getRefreshToken()]
        );
    }
}
