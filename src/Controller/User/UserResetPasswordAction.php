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
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserResetPasswordAction extends AbstractController
{
    public const MIN_LENGTH_PASSWORD = 6;

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
            throw new BadRequestHttpException('Error');
        }

        if (strlen($newPassword) < self::MIN_LENGTH_PASSWORD) {
            throw new BadRequestHttpException("Password must contain at least " . self::MIN_LENGTH_PASSWORD . " characters");
        }

        $secretKey = $secretKeyRepository->findOneBySecurityKey($secretKeyString);
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
