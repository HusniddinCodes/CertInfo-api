<?php

declare(strict_types=1);

namespace App\Component\Certificate;

use App\Entity\Certificate;
use App\Entity\MediaObject;
use Symfony\Component\HttpFoundation\Request;

class CertificatePdf
{
    public function __construct(private readonly PdfService $pdfService)
    {
    }

    public function create(
        Request $request,
        Certificate $certificate,
        CertificateChangeDataDto|CertificateCreateDto $certificateDataDto,
        string $projectDirectory
    ): MediaObject {
        return $this->pdfService->generatePdf(
            $projectDirectory,
            $certificateDataDto->getCourseFinishedDate()->format('Y'),
            $certificateDataDto->getFamilyName(),
            $certificateDataDto->getGivenName(),
            $certificateDataDto->getCourse()->getName(),
            $request->headers->get(
                'referer'
            ) . 'scan-qr/certificate?certificate=' . $certificate->getCertificateHash()
        );
    }
}
