<?php

declare(strict_types=1);


namespace App\Controller\Certificate;

use App\Controller\Base\AbstractController;
use App\Entity\Certificate;
use App\Repository\CertificateRepository;
use Symfony\Component\HttpFoundation\Request;

class GetCertificateByHashAction extends AbstractController
{
    public function __invoke(Request $request, CertificateRepository $certificateRepository): Certificate
    {
        $hash = $request->get('id');

        return $certificateRepository->findOneByHash($hash);
    }
}
