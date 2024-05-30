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
        User $createdBy
    ): Certificate {
        return (new Certificate())
            ->setOwner($user)
            ->setCourse($course)
            ->setCourseFinishedDate($date)
            ->setFile($mediaObject)
            ->setPracticeDescription($practiceDescription)
            ->setCertificateDefense($certificateDefense)
            ->setCreatedBy($createdBy);
    }
}
