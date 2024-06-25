<?php

declare(strict_types=1);

namespace App\Component\Certificate;

use App\Entity\MediaObject;

class CertificateImage
{
    public function __construct(private readonly PdfToJpgService $pdfToJpgService)
    {
    }

    public function create(
        MediaObject $pdf,
        CertificateChangeDataDto|CertificateCreateDto $certificateDataDto
    ): MediaObject {
        return $this->pdfToJpgService->pdfToImage(
            $certificateDataDto->getFamilyName(),
            $certificateDataDto->getGivenName(),
            '/tmp/' . $pdf->file->getBasename()
        );
    }
}
