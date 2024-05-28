<?php

declare(strict_types=1);

namespace App\Component\Certificate;


use App\Entity\MediaObject;
use Knp\Snappy\Pdf;
use Twig\Environment;
use Vich\UploaderBundle\FileAbstraction\ReplacingFile;

readonly class PdfService
{
    public function __construct(
        private QrCodeGenerateService $qrCodeGenerateService,
        private Pdf $pdf,
        private Environment $twig
    ) {
    }

    public function generatePdf(
        string $parameter,
        string $certificate_year,
        string $familyName,
        string $givenName,
        string $courseName,
        string $url
    ): MediaObject {
        $imagePath = $parameter . '/public/assets/certificateTemplate.svg';
        $imageLogo = $parameter . '/public/assets/Group 360.svg';
        $imageLogoName = $parameter . '/public/assets/KadirovDev.svg';
        $signature = $parameter . '/public/assets/Signature 2.svg';
        $cssPath = $parameter . '/public/assets/styles/style.css';
        $imageQrCode = $this->qrCodeGenerateService->generate($url);

        $html = $this->twig->render('certificate.html.twig', [
            'image_qr_code' => $imageQrCode,
            'image_base' => $imagePath,
            'image_logo' => $imageLogo,
            'image_logo_name' => $imageLogoName,
            'signature' => $signature,
            'certificate_year' => $certificate_year,
            'familyName' => $familyName,
            'givenName' => $givenName,
            'courseName' => $courseName,
            'cssPath' => $cssPath
        ]);

        $this->pdfSetOptions();

        return $this->createPdfFile($familyName, $givenName, $this->pdf->getOutputFromHtml($html));
    }

    private function pdfSetOptions(): void
    {
        $this->pdf->setOption('page-size', 'A4');
        $this->pdf->setOption('orientation', 'Portrait');
        $this->pdf->setOption('margin-top', '0mm');
        $this->pdf->setOption('margin-right', '0mm');
        $this->pdf->setOption('margin-bottom', '0mm');
        $this->pdf->setOption('margin-left', '0mm');
        $this->pdf->setOption('dpi', 600);
        $this->pdf->setOption('enable-local-file-access', true);
        $this->pdf->setOption('lowquality', false);
    }

    private function createPdfFile(string $familyName, string $givenName, string $pdfContent): MediaObject
    {
        $tempDir = sys_get_temp_dir();
        $tempPdfPath = $tempDir . DIRECTORY_SEPARATOR . $familyName . '_' . $givenName . '.pdf';
        file_put_contents($tempPdfPath, $pdfContent);

        $pdfFile = new ReplacingFile($tempPdfPath);
        $file = (new MediaObject());
        $file->file = $pdfFile;

        return $file;
    }
}
