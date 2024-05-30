<?php

declare(strict_types=1);

namespace App\Component\Certificate;

use App\Component\User\UserWithPersonBuilder;
use App\Entity\Certificate;
use App\Entity\Course;
use App\Entity\MediaObject;
use App\Entity\User;
use App\Repository\UserRepository;
use DateTimeInterface;

readonly class CertificateWithUserBuilder
{
    public function __construct(
        private UserRepository $userRepository,
        private UserWithPersonBuilder $userWithPersonBuilder,
        private CertificateFactory $certificateFactory,
        private CertificateManager $certificateManager
    ) {
    }

    public function make(
        string $email,
        string $familyName,
        string $givenName,
        Course $course,
        DateTimeInterface $finishedDate,
        string $practiceDescription,
        string $certificateDefense,
        User $createdBy,
        ?MediaObject $avatar
    ): Certificate {
        $user = $this->userRepository->findOneByEmail($email);

        if (!$user) {
            $user = $this->userWithPersonBuilder->make(
                $email,
                '123456',
                $familyName,
                $givenName,
                $avatar
            );
        }

        $certificate = $this->certificateFactory->create(
            $user,
            $course,
            $finishedDate,
            null,
            $practiceDescription,
            $certificateDefense,
            $createdBy
        );

        $this->certificateManager->save($certificate, true);

        return $certificate;
    }
}
