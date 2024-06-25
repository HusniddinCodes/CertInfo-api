<?php

declare(strict_types=1);

namespace App\Component\Certificate;

use App\Entity\Certificate;

class CertificateCheckNewData
{
    public function hasCertificateOwnerEmailChanged(
        Certificate $certificate,
        CertificateChangeDataDto $certificateChangeDataDto
    ): bool {
        $previousEmail = $certificate->getOwner()->getEmail();

        return $previousEmail !== $certificateChangeDataDto->getEmail();
    }

    public function hasCertificateOwnerPersonChanged(
        Certificate $certificate,
        CertificateChangeDataDto $certificateChangeDataDto
    ): bool {
        $previousFamilyName = $certificate->getOwner()->getPerson()->getFamilyName();
        $previousGivenName = $certificate->getOwner()->getPerson()->getGivenName();
        $previousCourse = $certificate->getCourse();

        return $previousFamilyName !== $certificateChangeDataDto->getFamilyName() ||
            $previousGivenName !== $certificateChangeDataDto->getGivenName() ||
            $previousCourse !== $certificateChangeDataDto->getCourse();
    }
}
