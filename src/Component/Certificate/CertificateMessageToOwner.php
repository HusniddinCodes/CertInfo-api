<?php

declare(strict_types=1);

namespace App\Component\Certificate;

use App\Entity\Certificate;
use App\Message\GreetingNewCertificateByEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;

class CertificateMessageToOwner
{
    public function __construct(private readonly MessageBusInterface $messageBus) {
    }

    public function sendEmail(
        Request $request,
        Certificate $certificate,
        $certificateImage,
        CertificateChangeDataDto|CertificateCreateDto $certificateDataDto
    ): void {
        $this->messageBus->dispatch(
            new GreetingNewCertificateByEmail(
                $certificateDataDto->getEmail(),
                $request->headers->get(
                    'referer'
                ) . 'scan-qr/certificate?certificate=' . $certificate->getCertificateHash(),
                $request->getSchemeAndHttpHost() . '/media/' . $certificate->getFile()->filePath,
                $request->getSchemeAndHttpHost() . '/media/' . $certificateImage->filePath,
                $certificateDataDto->getFamilyName(),
                $certificateDataDto->getGivenName(),
            )
        );
    }
}
