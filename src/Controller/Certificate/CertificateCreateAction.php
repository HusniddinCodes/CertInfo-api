<?php

declare(strict_types=1);

namespace App\Controller\Certificate;

use ApiPlatform\Validator\ValidatorInterface;
use App\Component\Certificate\CertificateCreateDto;
use App\Component\Certificate\CertificateFactory;
use App\Component\Certificate\CertificateManager;
use App\Component\Certificate\CertificateWithUserBuilder;
use App\Component\Certificate\PdfService;
use App\Component\User\CurrentUser;
use App\Component\User\UserWithPersonBuilder;
use App\Controller\Base\AbstractController;
use App\Entity\Certificate;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
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
        CertificateFactory $certificateFactory,
        CertificateManager $certificateManager,
        CertificateWithUserBuilder $certificateWithUserBuilder
    ): Certificate {
        /** @var CertificateCreateDto $certificateCreateRequest */
        $certificateCreateRequest = $this->getDtoFromRequest($request, CertificateCreateDto::class);

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
            $this->getParameter('kernel.project_dir'),
            $certificateCreateRequest->getCourseFinishedDate()->format('Y'),
            $certificateCreateRequest->getFamilyName(),
            $certificateCreateRequest->getGivenName(),
            $certificateCreateRequest->getCourse()->getName(),
            $request->headers->get('referer') . '/scan-qr/' . $certificate->getId()
        );

        $certificate->setFile($pdf);
        $certificate->setUpdatedBy($this->getUser());
        $certificateManager->save($certificate, true);

        return $certificate;
    }
}
