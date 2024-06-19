<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Component\Certificate\CertificateCreateDto;
use App\Controller\Certificate\CertificateCreateAction;
use App\Controller\Certificate\GetCertificateByHashAction;
use App\Controller\DeleteAction;
use App\Entity\Interfaces\CreatedAtSettableInterface;
use App\Entity\Interfaces\CreatedBySettableInterface;
use App\Entity\Interfaces\DeletedAtSettableInterface;
use App\Entity\Interfaces\DeletedBySettableInterface;
use App\Entity\Interfaces\UpdatedAtSettableInterface;
use App\Entity\Interfaces\UpdatedBySettableInterface;
use App\Filter\SearchMultiFieldsFilter;
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
        new Get(normalizationContext: ['groups' => ['certificate:forId:read']]),
        new Put(security: "is_granted('ROLE_ADMIN')"),
        new Delete(controller: DeleteAction::class, security: "is_granted('ROLE_ADMIN')"),
        new Get(
            uriTemplate: 'certificates/hash/{id}',
            requirements: ['id' => '[\w]+'],
            controller: GetCertificateByHashAction::class,
            normalizationContext: ['groups' => ['certificate:read']],
            read: false,
        )
    ],
    normalizationContext: ['groups' => ['certificate:read']],
    denormalizationContext: ['groups' => ['certificate:write']],
    paginationItemsPerPage: 8
)]
#[ApiFilter(DateFilter::class, properties: ['courseFinishedDate'])]
#[ApiFilter(SearchMultiFieldsFilter::class, properties: [
    'owner.email',
    'owner.person.familyName',
    'owner.person.givenName',
    'course.name',
    'practiceDescription',
    'certificateDefense'
])]
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
    #[Groups(['certificate:forId:read', 'certificate:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'certificates')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['certificate:read', 'certificate:write', 'certificate:forId:read'])]
    private ?User $owner = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\MediaObject", cascade: ["persist"])]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['certificate:forId:read'])]
    private ?MediaObject $file = null;

    #[ORM\ManyToOne(inversedBy: 'certificates')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['certificate:read', 'certificate:write', 'certificate:forId:read'])]
    private ?Course $course = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['certificate:read', 'certificate:write', 'certificate:forId:read'])]
    private ?\DateTimeInterface $courseFinishedDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['certificate:read', 'certificate:write', 'certificate:forId:read'])]
    private ?string $practiceDescription = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['certificate:read', 'certificate:write', 'certificate:forId:read'])]
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

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['certificate:forId:read'])]
    private ?MediaObject $imgCertificate = null;

    #[ORM\Column(length: 255)]
    private ?string $certificateHash = null;

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

    public function setCreatedBy(?UserInterface $user): self
    {
        $this->createdBy = $user;

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

    public function setUpdatedBy(?UserInterface $user): self
    {
        $this->updatedBy = $user;

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

    public function setDeletedBy(?UserInterface $user): self
    {
        $this->deletedBy = $user;

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

    public function getImgCertificate(): ?MediaObject
    {
        return $this->imgCertificate;
    }

    public function setImgCertificate(MediaObject $imgCertificate): self
    {
        $this->imgCertificate = $imgCertificate;

        return $this;
    }

    public function getCertificateHash(): ?string
    {
        return $this->certificateHash;
    }

    public function setCertificateHash(string $certificateHash): self
    {
        $this->certificateHash = $certificateHash;

        return $this;
    }
}
