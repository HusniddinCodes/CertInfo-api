<?php

declare(strict_types=1);

namespace App\Component\Certificate;

use App\Entity\Certificate;
use App\Entity\User;
use App\Repository\MediaObjectRepository;
use Symfony\Component\HttpFoundation\Request;

class NewCertificateFile
{
    public function __construct(
        private readonly MediaObjectRepository $mediaObjectRepository,
        private readonly CertificatePdf $certificatePdf,
        private readonly CertificateImage $certificateImage,
        private readonly CertificateManager $certificateManager,
        private readonly CertificateMessageToOwner $certificateMessageToOwner
    ) {
    }

    public function create(
        Request $request,
        Certificate $certificate,
        CertificateChangeDataDto|CertificateCreateDto $certificateDataDto,
        string $projectDirectory,
        User $createdBy
    ): void {
        $pdf = $this->certificatePdf->create($request, $certificate, $certificateDataDto, $projectDirectory);
        $certificateImage = $this->certificateImage->create($pdf, $certificateDataDto);

        $this->ifHasFiles($certificate);

        $certificate->setFile($pdf);
        $certificate->setImgCertificate($certificateImage);
        $certificate->setUpdatedBy($createdBy);

        $this->certificateManager->save($certificate, true);

        $this->certificateMessageToOwner->sendEmail($request, $certificate, $certificateImage, $certificateDataDto);
    }

    private function ifHasFiles(Certificate $certificate): void
    {
        if ($certificate->getFile() && $certificate->getImgCertificate() !== null) {
            $this->mediaObjectRepository->remove($certificate->getFile());
            $this->mediaObjectRepository->remove($certificate->getImgCertificate());
        }
    }
}
