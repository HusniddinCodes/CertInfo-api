<?php

declare(strict_types=1);

namespace App\Component\Certificate;

use App\Component\Person\PersonManager;
use App\Component\User\UserWithPersonBuilder;
use App\Entity\Certificate;
use App\Entity\User;
use App\Repository\PersonRepository;
use App\Repository\UserRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

readonly class CertificateChangeDataService
{
    public function __construct(
        private UserWithPersonBuilder $userWithPersonBuilder,
        private UserRepository $userRepository,
        private PersonManager $personManager,
        private CertificateFactory $certificateFactory,
        private ParameterBagInterface $params,
        private PersonRepository $personRepository,
    ) {
    }

    public function changeDataExceptUser(
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
        CertificateChangeDataDto $certificateChangeDataDto,
        User $createdBy
    ): Certificate {
        $user = $this->createUserIfNotExist($certificateChangeDataDto);

        return $this->certificateFactory->reCreateCertificate(
            $certificate,
            $user,
            $createdBy,
            $certificateChangeDataDto->getCourse(),
            $certificateChangeDataDto->getPracticeDescription(),
            $certificateChangeDataDto->getCertificateDefense(),
            $certificateChangeDataDto->getCourseFinishedDate()
        );
    }

    private function createUserIfNotExist(CertificateChangeDataDto $certificateChangeDataDto): User
    {
        $user = $this->userRepository->findOneByEmail($certificateChangeDataDto->getEmail());
        $avatar = $certificateChangeDataDto->getAvatar();

        if (null !== $this->personRepository->findOneByAvatar($avatar)) {
            $avatar = null;
        }

        if ($user === null) {
            return $this->userWithPersonBuilder->make(
                $certificateChangeDataDto->getEmail(),
                $this->params->get('default_password_for_student'),
                $certificateChangeDataDto->getFamilyName(),
                $certificateChangeDataDto->getGivenName(),
                $avatar
            );
        }

        return $user;
    }

    public function updatePersonIfHasChanged(
        Certificate $certificate,
        CertificateChangeDataDto $certificateChangeDataDto
    ): User {
        $user = $certificate->getOwner();
        $person = $user->getPerson();
        $familyName = $certificateChangeDataDto->getFamilyName();
        $givenName = $certificateChangeDataDto->getGivenName();

        if ($familyName !== $user->getPerson()->getFamilyName() || $givenName !== $user->getPerson()->getGivenName()) {
            $person
                ->setFamilyName($familyName)
                ->setGivenName($givenName);

            $this->personManager->save($person);
        }

        return $user;
    }
}
