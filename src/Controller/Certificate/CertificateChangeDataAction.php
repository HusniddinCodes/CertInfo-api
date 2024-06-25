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
        private readonly CertificateChangeDataDto $certificateChangeDataDto,
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
    ): Certificate {
        $this->validate($this->certificateChangeDataDto);

        if ($this->hasCertificateOwnerChanged($certificate)) {
            $this->createCertificate($certificate);
            $this->createNewCertificateFile($request, $certificate);
        } else {
            $this->certificateChangeDataService->certificateChangeData(
                $certificate,
                $this->certificateChangeDataDto->getPracticeDescription(),
                $this->certificateChangeDataDto->getCertificateDefense(),
                $this->certificateChangeDataDto->getCourseFinishedDate()
            );

            $this->certificateManager->save($certificate, true);
        }

        return $certificate;
    }

    private function hasCertificateOwnerChanged(Certificate $certificate)
    {
        $previousEmail = $certificate->getOwner()->getEmail();
        $previousFamilyName = $certificate->getOwner()->getPerson()->getFamilyName();
        $previousGivenName = $certificate->getOwner()->getPerson()->getGivenName();
        $previousCourse = $certificate->getCourse();

        return $previousEmail !== $this->certificateChangeDataDto->getEmail() ||
            $previousFamilyName !== $this->certificateChangeDataDto->getFamilyName() ||
            $previousGivenName !== $this->certificateChangeDataDto->getGivenName() ||
            $previousCourse !== $this->certificateChangeDataDto->getCourse();
    }

    private function createCertificate(Certificate $certificate)
    {
        return $this->certificateChangeDataService->ifNewOwnerData(
            $certificate,
            $this->certificateChangeDataDto->getEmail(),
            $this->certificateChangeDataDto->getFamilyName(),
            $this->certificateChangeDataDto->getGivenName(),
            $this->certificateChangeDataDto->getCourse(),
            $this->certificateChangeDataDto->getCourseFinishedDate(),
            $this->certificateChangeDataDto->getPracticeDescription(),
            $this->certificateChangeDataDto->getCertificateDefense(),
            $this->getUser(),
            $this->certificateChangeDataDto->getAvatar()
        );
    }

    private function createCertificatePdf(Request $request, Certificate $certificate)
    {
        $projectDirectory = $this->getParameter('kernel.project_dir');

        return $this->pdfService->generatePdf(
            $projectDirectory,
            $this->certificateChangeDataDto->getCourseFinishedDate()->format('Y'),
            $this->certificateChangeDataDto->getFamilyName(),
            $this->certificateChangeDataDto->getGivenName(),
            $this->certificateChangeDataDto->getCourse()->getName(),
            $request->headers->get(
                'referer'
            ) . 'scan-qr/certificate?certificate=' . $certificate->getCertificateHash()
        );
    }

    private function createCertificateImage($pdf)
    {
        return $this->pdfToJpgService->pdfToImage(
            $this->certificateChangeDataDto->getFamilyName(),
            $this->certificateChangeDataDto->getGivenName(),
            '/tmp/' . $pdf->file->getBasename()
        );
    }

    private function sendEmail(Request $request, Certificate $certificate, $certificateImage)
    {
        $this->messageBus->dispatch(
            new GreetingNewCertificateByEmail(
                $this->certificateChangeDataDto->getEmail(),
                $request->headers->get(
                    'referer'
                ) . 'scan-qr/certificate?certificate=' . $certificate->getCertificateHash(),
                $request->getSchemeAndHttpHost() . '/media/' . $certificate->getFile()->filePath,
                $request->getSchemeAndHttpHost() . '/media/' . $certificateImage->filePath,
                $this->certificateChangeDataDto->getFamilyName(),
                $this->certificateChangeDataDto->getGivenName(),
            )
        );
    }

    private function createNewCertificateFile(Request $request, Certificate $certificate)
    {
        $pdf = $this->createCertificatePdf($request, $certificate);
        $certificateImage= $this->createCertificateImage($pdf);

        $this->mediaObjectRepository->remove($certificate->getFile());
        $this->mediaObjectRepository->remove($certificate->getImgCertificate());

        $certificate->setFile($pdf);
        $certificate->setImgCertificate($certificateImage);
        $certificate->setUpdatedBy($this->getUser());

        $this->certificateManager->save($certificate, true);

        $this->sendEmail($request, $certificate, $certificateImage);
    }
}
