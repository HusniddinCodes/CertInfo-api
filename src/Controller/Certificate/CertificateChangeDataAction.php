<?php

declare(strict_types=1);

namespace App\Controller\Certificate;

use ApiPlatform\Validator\ValidatorInterface;
use App\Component\Certificate\CertificateChangeDataDto;
use App\Component\Certificate\CertificateChangeDataService;
use App\Component\Certificate\CertificateCheckNewData;
use App\Component\Certificate\CertificateManager;
use App\Component\Certificate\NewCertificateFile;
use App\Component\User\CurrentUser;
use App\Controller\Base\AbstractController;
use App\Entity\Certificate;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class CertificateChangeDataAction extends AbstractController
{
    public function __construct(
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        CurrentUser $currentUser,
        private readonly CertificateChangeDataService $certificateChangeData,
        private readonly CertificateManager $certificateManager,
        private readonly CertificateCheckNewData $certificateCheckNewData,
        private readonly NewCertificateFile $newCertificateFile
    ) {
        parent::__construct($serializer, $validator, $currentUser);
    }

    public function __invoke(
        Certificate $certificate,
        Request $request,
        CertificateChangeDataDto $dto
    ): Certificate {
        $this->validate($dto);
        $this->changeCertificateOrUser($request, $certificate, $dto, $this->getUser());

        return $certificate;
    }

    private function changeCertificateOrUser(
        Request $request,
        Certificate $certificate,
        CertificateChangeDataDto $dto,
        User $createdBy
    ): void {
        $projectDirectory = $this->getParameter('kernel.project_dir');

        if ($this->certificateCheckNewData->hasCertificateOwnerEmailChanged($certificate, $dto)) {
            $this->certificateChangeData->createUserIfNotFind($certificate, $dto, $createdBy);
            $this->newCertificateFile->create($request, $certificate, $dto, $projectDirectory, $createdBy);
        } elseif ($this->certificateCheckNewData->hasCertificateOwnerPersonChanged($certificate, $dto)) {
            $this->certificateChangeData->updatePersonIfHasChanged($certificate, $dto);
            $this->newCertificateFile->create($request, $certificate, $dto, $projectDirectory, $createdBy);
        } else {
            $this->certificateChangeData->changeDataExceptUser($certificate, $dto);
            $this->certificateManager->save($certificate, true);
        }
    }
}
