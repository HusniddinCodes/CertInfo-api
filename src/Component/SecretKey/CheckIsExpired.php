<?php

declare(strict_types=1);

namespace App\Component\SecretKey;

use DateTime;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CheckIsExpired
{
    private const TIME_TO_EXPIRE = 1;

    public function isExpiredResetPasswordSecretKey($secretKey, $secretKeyRepository): void
    {
        if ($secretKey === null) {
            throw new BadRequestHttpException('The link is outdated or unavailable!');
        }

        $user = $secretKey->getUser();

        if ($user === null) {
            throw new BadRequestHttpException('This user does not exist in the system!');
        }

        $createdAt = $secretKey->getCreatedAt();
        $interval = (new DateTime())->diff($createdAt);

        if ($interval->h >= self::TIME_TO_EXPIRE) {
            $secretKeyRepository->remove($secretKey, true);
            throw new BadRequestHttpException('The link is out of date');
        }
    }
}
