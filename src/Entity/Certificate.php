<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Interfaces\CreatedAtSettableInterface;
use App\Entity\Interfaces\CreatedBySettableInterface;
use App\Entity\Interfaces\DeletedAtSettableInterface;
use App\Entity\Interfaces\DeletedBySettableInterface;
use App\Entity\Interfaces\UpdatedAtSettableInterface;
use App\Entity\Interfaces\UpdatedBySettableInterface;
use App\Repository\CertificateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CertificateRepository::class)]
#[ApiResource(
    operations: [
        new Post(),
        new GetCollection(),
        new Put(),
        new Delete(),
        new Get(),
    ],
    normalizationContext: ['groups' => ['certificate:read']],
    denormalizationContext: ['groups' => ['certificate:write']]
)]
class Certificate implements
    CreatedAtSettableInterface,
    CreatedBySettableInterface,
    UpdatedAtSettableInterface,
    UpdatedBySettableInterface,
    DeletedAtSettableInterface,
    DeletedBySettableInterface

{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'certificates')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['certificate:read'])]
    private ?MediaObject $file = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['certificate:read', 'certificate:write'])]
    private ?Course $course = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['certificate:read', 'certificate:write'])]
    private ?\DateTimeInterface $courseFinishedDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['certificate:read', 'certificate:write'])]
    private ?string $practiceDescription = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['certificate:read', 'certificate:write'])]
    private ?string $certificateDefense = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['certificate:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['certificate:read'])]
    private ?User $createdBy = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['certificate:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne]
    #[Groups(['certificate:read'])]
    private ?User $updatedBy = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['certificate:read'])]
    private ?\DateTimeImmutable $deletedAt = null;

    #[ORM\ManyToOne]
    private ?User $deletedBy = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getFile(): ?MediaObject
    {
        return $this->file;
    }

    public function setFile(?MediaObject $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): self
    {
        $this->course = $course;

        return $this;
    }

    public function getCourseFinishedDate(): ?\DateTimeInterface
    {
        return $this->courseFinishedDate;
    }

    public function setCourseFinishedDate(\DateTimeInterface $courseFinishedDate): self
    {
        $this->courseFinishedDate = $courseFinishedDate;

        return $this;
    }

    public function getPracticeDescription(): ?string
    {
        return $this->practiceDescription;
    }

    public function setPracticeDescription(?string $practiceDescription): self
    {
        $this->practiceDescription = $practiceDescription;

        return $this;
    }

    public function getCertificateDefense(): ?string
    {
        return $this->certificateDefense;
    }

    public function setCertificateDefense(?string $certificateDefense): self
    {
        $this->certificateDefense = $certificateDefense;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(User|\Symfony\Component\Security\Core\User\UserInterface|null $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(User|\Symfony\Component\Security\Core\User\UserInterface|null $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(\DateTimeInterface|null $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getDeletedBy(): ?User
    {
        return $this->deletedBy;
    }

    public function setDeletedBy(User|\Symfony\Component\Security\Core\User\UserInterface|null $deletedBy): self
    {
        $this->deletedBy = $deletedBy;

        return $this;
    }
}
