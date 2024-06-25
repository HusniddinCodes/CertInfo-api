<?php

declare(strict_types=1);

namespace App\Component\Certificate;

use App\Entity\Certificate;
use App\Entity\Course;
use App\Entity\MediaObject;
use App\Entity\User;
use DateTimeInterface;

class CertificateFactory
{
    public function create(
        User $user,
        Course $course,
        DateTimeInterface $date,
        ?MediaObject $mediaObject,
        string $practiceDescription,
        string $certificateDefense,
        User $createdBy,
        string $certificateHash
    ): Certificate {
        return (new Certificate())
            ->setOwner($user)
            ->setCourse($course)
            ->setCourseFinishedDate($date)
            ->setFile($mediaObject)
            ->setPracticeDescription($practiceDescription)
            ->setCertificateDefense($certificateDefense)
            ->setCreatedBy($createdBy)
            ->setCertificateHash($certificateHash);
    }

    public function reCreateCertificate(
        Certificate $certificate,
        User $user,
        User $createdBy,
        Course $course,
        string $practiceDescription,
        string $certificateDefense,
        DateTimeInterface $finishedDate

    ): Certificate {
        return $certificate
            ->setOwner($user)
            ->setCreatedBy($createdBy)
            ->setCourse($course)
            ->setPracticeDescription($practiceDescription)
            ->setCertificateDefense($certificateDefense)
            ->setCourseFinishedDate($finishedDate);
    }
}
