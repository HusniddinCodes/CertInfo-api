<?php

declare(strict_types=1);

namespace App\Component\Certificate;

use App\Component\Person\PersonManager;
use App\Component\User\UserWithPersonBuilder;
use App\Entity\Certificate;
use App\Entity\Course;
use App\Entity\MediaObject;
use App\Entity\Person;
use App\Entity\User;
use App\Repository\UserRepository;
use DateTimeInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CertificateChangeDataService
{
    public function __construct(
        private UserWithPersonBuilder $userWithPersonBuilder,
        private UserRepository $userRepository,
        private PersonManager $personManager,
        private ParameterBagInterface $params
    ) {
    }

    public function certificateChangeData(
        Certificate $certificate,
        CertificateChangeDataDto $certificateChangeDataDto
    ): Certificate {
        return $certificate
            ->setPracticeDescription($certificateChangeDataDto->getPracticeDescription())
            ->setCertificateDefense($certificateChangeDataDto->getCertificateDefense())
            ->setCourseFinishedDate($certificateChangeDataDto->getCourseFinishedDate());
    }

    public function createUserIfNotFind(
        Certificate $certificate,
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

        if ($user === null) {
            $user = $this->userWithPersonBuilder->make(
                $email,
                $this->params->get('default_password_for_student'),
                $familyName,
                $givenName,
                $avatar
            );
        }

        return $this->createCertificate(
            $certificate,
            $user,
            $createdBy,
            $course,
            $practiceDescription,
            $certificateDefense,
            $finishedDate
        );
    }

    public function updatePersonIfHasChanged(User $user, string $familyName, string $givenName,)
    {
        $person = $user->getPerson();

        if ($familyName !== $user->getPerson()->getFamilyName() || $givenName !== $user->getPerson()->getGivenName()) {
            $person
                ->setFamilyName($familyName)
                ->setGivenName($givenName);

            $this->personManager->save($person);
        }

        return $user;
    }

    private function createCertificate(
        Certificate $certificate,
        User $user,
        User $createdBy,
        Course $course,
        string $practiceDescription,
        string $certificateDefense,
        DateTimeInterface $finishedDate

    ): Certificate {
        return $certificate
            ->setOwner($user)
            ->setCreatedBy($createdBy)
            ->setCourse($course)
            ->setPracticeDescription($practiceDescription)
            ->setCertificateDefense($certificateDefense)
            ->setCourseFinishedDate($finishedDate);
    }
}
