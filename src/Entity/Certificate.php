<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Component\Certificate\CertificateCreateDto;
use App\Controller\Certificate\CertificateCreateAction;
use App\Controller\DeleteAction;
use App\Entity\Interfaces\CreatedAtSettableInterface;
use App\Entity\Interfaces\CreatedBySettableInterface;
use App\Entity\Interfaces\DeletedAtSettableInterface;
use App\Entity\Interfaces\DeletedBySettableInterface;
use App\Entity\Interfaces\UpdatedAtSettableInterface;
use App\Entity\Interfaces\UpdatedBySettableInterface;
use App\Repository\CertificateRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CertificateRepository::class)]
#[ApiResource(
    operations: [
        new Post(
            controller: CertificateCreateAction::class,
            security: "is_granted('ROLE_ADMIN')",
            input: CertificateCreateDto::class,
        ),
        new GetCollection(security: "is_granted('ROLE_ADMIN')"),
        new Get(security: "is_granted('ROLE_ADMIN')"),
        new Put(security: "is_granted('ROLE_ADMIN')"),
        new Delete(controller: DeleteAction::class, security: "is_granted('ROLE_ADMIN')"),
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
    #[Groups(['certificate:read', 'certificate:write'])]
    private ?User $owner = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\MediaObject", cascade: ["persist"])]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(["certificate:read"])]
    private ?MediaObject $file = null;

    #[ORM\ManyToOne(inversedBy: 'certificates')]
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

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['certificate:read'])]
    private ?User $createdBy = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['certificate:read'])]
    private ?User $updatedBy = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['certificate:read'])]
    private ?User $deletedBy = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['certificate:read'])]
    private ?DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['certificate:read'])]
    private ?DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['certificate:read'])]
    private ?DateTimeInterface $deletedAt = null;


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

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?UserInterface $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt?->format('d-m-Y H:i');
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?UserInterface $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt?->format('d-m-Y H:i');
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDeletedBy(): ?User
    {
        return $this->deletedBy;
    }

    public function setDeletedBy(?UserInterface $deletedBy): self
    {
        $this->deletedBy = $deletedBy;

        return $this;
    }

    public function getDeletedAt(): ?string
    {
        return $this->deletedAt?->format('d-m-Y H:i');
    }

    public function setDeletedAt(?DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }
}
