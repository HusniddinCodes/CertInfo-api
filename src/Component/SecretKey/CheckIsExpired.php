<?php

declare(strict_types=1);

namespace App\Component\SecretKey;

use DateTime;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CheckIsExpired
{
    public function isExpiredResetPasswordSecretKey($secretKey, $secretKeyRepository): void
    {
        if ($secretKey === null) {
            throw new HttpException(400, 'The link is outdated or unavailable!');
        }

        $user = $secretKey->getUser();

        if (!$user) {
            throw new BadRequestHttpException('This user does not exist in the system!');
        }

        $createdAt = $secretKey->getCreatedAt();
        $interval = (new DateTime())->diff($createdAt);

        if ($interval->h >= 1) {
            $secretKeyRepository->remove($secretKey, true);
            throw new BadRequestHttpException('The link is out of date');
        }
    }
}
