<?php

declare(strict_types=1);

namespace App\Component\Certificate;

use App\Entity\Course;
use App\Entity\MediaObject;
use DateTimeInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

readonly class CertificateChangeDataDto
{
    public function __construct(
        #[Assert\NotBlank(message: "bo'sh")]
        #[Assert\Email]
        #[Groups(['certificate:write'])]
        private string $email,

        #[Groups(['certificate:write'])]
        #[Assert\NotBlank]
        private string $familyName,

        #[Groups(['certificate:write'])]
        #[Assert\NotBlank]
        private string $givenName,

        #[Groups(['certificate:write'])]
        #[Assert\NotBlank]
        private Course $course,

        #[Groups(['certificate:write'])]
        #[Assert\NotBlank]
        private DateTimeInterface $courseFinishedDate,

        #[Groups(['certificate:write'])]
        private ?MediaObject $avatar = null,

        #[Groups(['certificate:write'])]
        private ?string $practiceDescription = null,

        #[Groups(['certificate:write'])]
        private ?string $certificateDefense = null,
    ) {
    }

    public function getAvatar(): ?MediaObject
    {
        return $this->avatar;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFamilyName(): string
    {
        return $this->familyName;
    }

    public function getGivenName(): string
    {
        return $this->givenName;
    }

    public function getCourse(): Course
    {
        return $this->course;
    }

    public function getCourseFinishedDate(): DateTimeInterface
    {
        return $this->courseFinishedDate;
    }

    public function getPracticeDescription(): ?string
    {
        return $this->practiceDescription;
    }

    public function getCertificateDefense(): ?string
    {
        return $this->certificateDefense;
    }
}
