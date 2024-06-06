<?php

declare(strict_types=1);

namespace App\Component\Certificate;

use App\Entity\MediaObject;
use Imagick;
use Vich\UploaderBundle\FileAbstraction\ReplacingFile;

class PdfToJpgService
{
    public function pdfToImage(string $familyName, string $givenName, string $pdf): MediaObject
    {
        $page = 0;
        $imagick = new Imagick();
        $imagick->setResolution(300, 300);
        $imagick->readImage(sprintf('%s[%d]', $pdf, $page));

        $imagick->setImageFormat('jpg');
        $imageData = $imagick->getImageBlob();
        $imagick->clear();
        $imagick->destroy();

        return $this->createJpgFile($familyName, $givenName, $imageData);
    }

    private function createJpgFile(string $familyName, string $givenName, string $imageData): MediaObject
    {
        $tempDir = sys_get_temp_dir();
        $tempJpgPath = $tempDir . DIRECTORY_SEPARATOR . $familyName . '_' . $givenName . '.jpg';
        file_put_contents($tempJpgPath, $imageData);

        $jpgFile = new ReplacingFile($tempJpgPath);
        $file = new MediaObject();
        $file->file = $jpgFile;

        return $file;
    }
}
