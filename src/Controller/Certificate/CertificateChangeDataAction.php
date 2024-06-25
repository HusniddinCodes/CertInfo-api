<?php

declare(strict_types=1);

namespace App\Controller\Certificate;

use ApiPlatform\Validator\ValidatorInterface;
use App\Component\Certificate\CertificateChangeDataDto;
use App\Component\Certificate\CertificateChangeDataService;
use App\Component\Certificate\CertificateFactory;
use App\Component\Certificate\CertificateManager;
use App\Component\Certificate\CertificateWithUserBuilder;
use App\Component\Certificate\PdfService;
use App\Component\Certificate\PdfToJpgService;
use App\Component\User\CurrentUser;
use App\Component\User\UserWithPersonBuilder;
use App\Controller\Base\AbstractController;
use App\Entity\Certificate;
use App\Message\GreetingNewCertificateByEmail;
use App\Repository\MediaObjectRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;

class CertificateChangeDataAction extends AbstractController
{
    public function __construct(
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        CurrentUser $currentUser,
        private readonly CertificateChangeDataService $certificateChangeDataService,
        private readonly PdfService $pdfService,
        private readonly PdfToJpgService $pdfToJpgService,
        private readonly MessageBusInterface $messageBus,
        private readonly CertificateManager $certificateManager,
        private readonly MediaObjectRepository $mediaObjectRepository
    ) {
        parent::__construct($serializer, $validator, $currentUser);
    }

    public function __invoke(
        Certificate $certificate,
        Request $request,
        UserRepository $userRepository,
        UserWithPersonBuilder $userWithPersonBuilder,
        CertificateFactory $certificateFactory,
        CertificateWithUserBuilder $certificateWithUserBuilder,
        CertificateChangeDataDto $certificateChangeDataDto
    ): Certificate {
        $this->validate($certificateChangeDataDto);

        if ($this->IfNewOwnerEmail($certificate, $certificateChangeDataDto)) {
            $this->hasCertificateOwnerEmailChanged($certificate, $certificateChangeDataDto);
            $this->createNewCertificateFile($request, $certificate, $certificateChangeDataDto);
        } elseif ($this->hasCertificateOwnerPersonChanged($certificate, $certificateChangeDataDto)) {
            $this->IfNewOwnerPerson($certificate, $certificateChangeDataDto);
            $this->createNewCertificateFile($request, $certificate, $certificateChangeDataDto);
        } else {
            $this->certificateChangeDataService->certificateChangeData($certificate, $certificateChangeDataDto);

            $this->certificateManager->save($certificate, true);
        }

        return $certificate;
    }

    private function hasCertificateOwnerEmailChanged(
        Certificate $certificate,
        CertificateChangeDataDto $certificateChangeDataDto
    ): bool {
        $previousEmail = $certificate->getOwner()->getEmail();

        return $previousEmail !== $certificateChangeDataDto->getEmail();
    }

    private function IfNewOwnerEmail(Certificate $certificate, CertificateChangeDataDto $certificateChangeDataDto)
    {
        return $this->certificateChangeDataService->createUserIfNotFind(
            $certificate,
            $certificateChangeDataDto->getEmail(),
            $certificateChangeDataDto->getFamilyName(),
            $certificateChangeDataDto->getGivenName(),
            $certificateChangeDataDto->getCourse(),
            $certificateChangeDataDto->getCourseFinishedDate(),
            $certificateChangeDataDto->getPracticeDescription(),
            $certificateChangeDataDto->getCertificateDefense(),
            $this->getUser(),
            $certificateChangeDataDto->getAvatar()
        );
    }

    private function hasCertificateOwnerPersonChanged(
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

    private function IfNewOwnerPerson(Certificate $certificate, CertificateChangeDataDto $certificateChangeDataDto)
    {
        return $this->certificateChangeDataService->updatePersonIfHasChanged(
            $certificate->getOwner(),
            $certificateChangeDataDto->getFamilyName(),
            $certificateChangeDataDto->getGivenName()
        );
    }

    private function createNewCertificateFile(
        Request $request,
        Certificate $certificate,
        CertificateChangeDataDto $certificateChangeDataDto
    ) {
        $pdf = $this->createCertificatePdf($request, $certificate, $certificateChangeDataDto);
        $certificateImage = $this->createCertificateImage($pdf, $certificateChangeDataDto);

        $this->mediaObjectRepository->remove($certificate->getFile());
        $this->mediaObjectRepository->remove($certificate->getImgCertificate());

        $certificate->setFile($pdf);
        $certificate->setImgCertificate($certificateImage);
        $certificate->setUpdatedBy($this->getUser());

        $this->certificateManager->save($certificate, true);

        $this->sendEmail($request, $certificate, $certificateImage, $certificateChangeDataDto);
    }

    private function createCertificatePdf(
        Request $request,
        Certificate $certificate,
        CertificateChangeDataDto $certificateChangeDataDto
    ) {
        $projectDirectory = $this->getParameter('kernel.project_dir');

        return $this->pdfService->generatePdf(
            $projectDirectory,
            $certificateChangeDataDto->getCourseFinishedDate()->format('Y'),
            $certificateChangeDataDto->getFamilyName(),
            $certificateChangeDataDto->getGivenName(),
            $certificateChangeDataDto->getCourse()->getName(),
            $request->headers->get(
                'referer'
            ) . 'scan-qr/certificate?certificate=' . $certificate->getCertificateHash()
        );
    }

    private function createCertificateImage($pdf, CertificateChangeDataDto $certificateChangeDataDto)
    {
        return $this->pdfToJpgService->pdfToImage(
            $certificateChangeDataDto->getFamilyName(),
            $certificateChangeDataDto->getGivenName(),
            '/tmp/' . $pdf->file->getBasename()
        );
    }

    private function sendEmail(
        Request $request,
        Certificate $certificate,
        $certificateImage,
        CertificateChangeDataDto $certificateChangeDataDto
    ) {
        $this->messageBus->dispatch(
            new GreetingNewCertificateByEmail(
                $certificateChangeDataDto->getEmail(),
                $request->headers->get(
                    'referer'
                ) . 'scan-qr/certificate?certificate=' . $certificate->getCertificateHash(),
                $request->getSchemeAndHttpHost() . '/media/' . $certificate->getFile()->filePath,
                $request->getSchemeAndHttpHost() . '/media/' . $certificateImage->filePath,
                $certificateChangeDataDto->getFamilyName(),
                $certificateChangeDataDto->getGivenName(),
            )
        );
    }
}
