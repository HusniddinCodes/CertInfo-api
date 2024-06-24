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
        private MediaObjectRepository $mediaObjectRepository
    ) {
        parent::__construct($serializer, $validator, $currentUser);
    }

    public function __invoke(
        Certificate $certificate,
        Request $request,
        UserRepository $userRepository,
        UserWithPersonBuilder $userWithPersonBuilder,
        PdfService $pdfService,
        PdfToJpgService $pdfToJpgService,
        CertificateFactory $certificateFactory,
        CertificateManager $certificateManager,
        CertificateWithUserBuilder $certificateWithUserBuilder,
        MessageBusInterface $messageBus,
        CertificateChangeDataDto $certificateChangeDataDto,
        CertificateChangeDataService $certificateChangeDataService
    ): Certificate {
        $this->validate($certificateChangeDataDto);
        $projectDirectory = $this->getParameter('kernel.project_dir');
        $previousEmail = $certificate->getOwner()->getEmail();
        $previousFamilyName = $certificate->getOwner()->getPerson()->getFamilyName();
        $previousGivenName = $certificate->getOwner()->getPerson()->getGivenName();
        $previousCourse = $certificate->getCourse();

        if (
            $previousEmail !== $certificateChangeDataDto->getEmail() ||
            $previousFamilyName !== $certificateChangeDataDto->getFamilyName() ||
            $previousGivenName !== $certificateChangeDataDto->getGivenName() ||
            $previousCourse !== $certificateChangeDataDto->getCourse()
        ) {
            $certificate = $certificateChangeDataService->ifNewOwnerData(
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

            $pdf = $pdfService->generatePdf(
                $projectDirectory,
                $certificateChangeDataDto->getCourseFinishedDate()->format('Y'),
                $certificateChangeDataDto->getFamilyName(),
                $certificateChangeDataDto->getGivenName(),
                $certificateChangeDataDto->getCourse()->getName(),
                $request->headers->get(
                    'referer'
                ) . 'scan-qr/certificate?certificate=' . $certificate->getCertificateHash()
            );

            $certificateImage = $pdfToJpgService->pdfToImage(
                $certificateChangeDataDto->getFamilyName(),
                $certificateChangeDataDto->getGivenName(),
                '/tmp/' . $pdf->file->getBasename()
            );

            $this->mediaObjectRepository->remove($certificate->getFile());
            $this->mediaObjectRepository->remove($certificate->getImgCertificate());

            $certificate->setFile($pdf);
            $certificate->setImgCertificate($certificateImage);
            $certificate->setUpdatedBy($this->getUser());

            $certificateManager->save($certificate, true);

            $messageBus->dispatch(
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
        } else {
            $certificateChangeDataService->certificateChangeData(
                $certificate,
                $certificateChangeDataDto->getPracticeDescription(),
                $certificateChangeDataDto->getCertificateDefense(),
                $certificateChangeDataDto->getCourseFinishedDate()
            );

            $certificateManager->save($certificate, true);
        }

        return $certificate;
    }
}
