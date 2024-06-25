<?php

declare(strict_types=1);

namespace App\Controller\Certificate;

use ApiPlatform\Validator\ValidatorInterface;
use App\Component\Certificate\CertificateCreateDto;
use App\Component\Certificate\CertificateWithUserBuilder;
use App\Component\Certificate\NewCertificateFile;
use App\Component\User\CurrentUser;
use App\Controller\Base\AbstractController;
use App\Entity\Certificate;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class CertificateCreateAction extends AbstractController
{
    public function __construct(
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        CurrentUser $currentUser,
        private readonly NewCertificateFile $newCertificateFile
    ) {
        parent::__construct($serializer, $validator, $currentUser);
    }

    public function __invoke(
        Request $request,
        CertificateWithUserBuilder $certificateWithUserBuilder,
        CertificateCreateDto $certificateCreateDto
    ): Certificate {
        $this->validate($certificateCreateDto);
        $projectDirectory = $this->getParameter('kernel.project_dir');
        $createdBy = $this->getUser();

        $certificate = $certificateWithUserBuilder->make(
            $certificateCreateDto->getEmail(),
            $certificateCreateDto->getFamilyName(),
            $certificateCreateDto->getGivenName(),
            $certificateCreateDto->getCourse(),
            $certificateCreateDto->getCourseFinishedDate(),
            $certificateCreateDto->getPracticeDescription(),
            $certificateCreateDto->getCertificateDefense(),
            $createdBy,
            $certificateCreateDto->getAvatar()
        );

        $this->newCertificateFile->create(
            $request,
            $certificate,
            $certificateCreateDto,
            $projectDirectory,
            $createdBy
        );

        return $certificate;
    }
}
