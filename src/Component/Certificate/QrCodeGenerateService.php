<?php

declare(strict_types=1);

namespace App\Component\Certificate;

use Endroid\QrCode\Builder\Builder;

readonly class QrCodeGenerateService
{
    public function generate(string $qrCodeData):string
    {
        $result = (new Builder())
            ->data($qrCodeData)
            ->build();

        return base64_encode($result->getString());
    }
}
