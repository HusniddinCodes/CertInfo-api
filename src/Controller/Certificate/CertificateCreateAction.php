<?php

declare(strict_types=1);

namespace App\Controller\Certificate;

use ApiPlatform\Validator\ValidatorInterface;
use App\Component\Certificate\CertificateCreateDto;
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
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;

class CertificateCreateAction extends AbstractController
{
    public function __construct(
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        CurrentUser $currentUser,
    ) {
        parent::__construct($serializer, $validator, $currentUser);
    }

    public function __invoke(
        Request $request,
        UserRepository $userRepository,
        UserWithPersonBuilder $userWithPersonBuilder,
        PdfService $pdfService,
        PdfToJpgService $pdfToJpgService,
        CertificateFactory $certificateFactory,
        CertificateManager $certificateManager,
        CertificateWithUserBuilder $certificateWithUserBuilder,
        MessageBusInterface $messageBus,
    ): Certificate {
        /** @var CertificateCreateDto $certificateCreateRequest */
        $certificateCreateRequest = $this->getDtoFromRequest($request, CertificateCreateDto::class);
        $projectDirectory = $this->getParameter('kernel.project_dir');

        $certificate = $certificateWithUserBuilder->make(
            $certificateCreateRequest->getEmail(),
            $certificateCreateRequest->getFamilyName(),
            $certificateCreateRequest->getGivenName(),
            $certificateCreateRequest->getCourse(),
            $certificateCreateRequest->getCourseFinishedDate(),
            $certificateCreateRequest->getPracticeDescription(),
            $certificateCreateRequest->getCertificateDefense(),
            $this->getUser(),
            $certificateCreateRequest->getAvatar()
        );

        $pdf = $pdfService->generatePdf(
            $projectDirectory,
            $certificateCreateRequest->getCourseFinishedDate()->format('Y'),
            $certificateCreateRequest->getFamilyName(),
            $certificateCreateRequest->getGivenName(),
            $certificateCreateRequest->getCourse()->getName(),
            $request->headers->get('referer') . '/scan-qr/' . $certificate->getId()
        );

        $certificateImage = $pdfToJpgService->pdfToImage(
            $certificateCreateRequest->getFamilyName(),
            $certificateCreateRequest->getGivenName(),
            '/tmp/' . $pdf->file->getBasename()
        );

        $certificate->setFile($pdf);
        $certificate->setImgCertificate($certificateImage);
        $certificate->setUpdatedBy($this->getUser());

        $certificateManager->save($certificate, true);

        $messageBus->dispatch(new GreetingNewCertificateByEmail(
            $certificateCreateRequest->getEmail(),
            $request->headers->get('referer') . '/scan-qr/' . $certificate->getId(),
            $request->getSchemeAndHttpHost() . '/media/' . $certificate->getFile()->filePath,
            $request->getSchemeAndHttpHost() . '/media/' . $certificateImage->filePath,
            $certificateCreateRequest->getFamilyName(),
            $certificateCreateRequest->getGivenName(),
        ));

        return $certificate;
    }
}
