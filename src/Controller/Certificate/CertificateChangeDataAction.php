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
        CertificateChangeDataDto $certificateChangeDataDto
    ): Certificate {
        $this->validate($certificateChangeDataDto);
        $projectDirectory = $this->getParameter('kernel.project_dir');
        $createdBy = $this->getUser();

        if ($this->certificateCheckNewData->hasCertificateOwnerEmailChanged($certificate, $certificateChangeDataDto)) {
            $this->certificateChangeData->createUserIfNotFind($certificate, $certificateChangeDataDto, $createdBy);

            $this->newCertificateFile->create(
                $request,
                $certificate,
                $certificateChangeDataDto,
                $projectDirectory,
                $createdBy
            );
        } elseif ($this->certificateCheckNewData->hasCertificateOwnerPersonChanged(
            $certificate,
            $certificateChangeDataDto
        )) {
            $this->certificateChangeData->updatePersonIfHasChanged($certificate, $certificateChangeDataDto);

            $this->newCertificateFile->create(
                $request,
                $certificate,
                $certificateChangeDataDto,
                $projectDirectory,
                $createdBy
            );
        } else {
            $this->certificateChangeData->changeDataExceptUser($certificate, $certificateChangeDataDto);

            $this->certificateManager->save($certificate, true);
        }

        return $certificate;
    }
}
