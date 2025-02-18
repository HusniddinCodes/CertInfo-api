<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model\Operation;
use App\Component\User\Dtos\RefreshTokenRequestDto;
use App\Component\User\Dtos\SignUpRequestDto;
use App\Component\User\Dtos\TokensDto;
use App\Component\User\Dtos\UserChangeDataDto;
use App\Component\User\Dtos\UserChangePasswordDto;
use App\Component\User\Dtos\UserResetPasswordDto;
use App\Controller\DeleteAction;
use App\Controller\User\UserAboutMeAction;
use App\Controller\User\UserAuthAction;
use App\Controller\User\UserAuthByRefreshTokenAction;
use App\Controller\User\UserChangeDataAction;
use App\Controller\User\UserChangePasswordAction;
use App\Controller\User\UserCreateAction;
use App\Controller\User\UserIsUniqueEmailAction;
use App\Controller\User\UserRequestResetPasswordAction;
use App\Controller\User\UserResetPasswordAction;
use App\Entity\Interfaces\CreatedAtSettableInterface;
use App\Entity\Interfaces\DeletedAtSettableInterface;
use App\Entity\Interfaces\UpdatedAtSettableInterface;
use App\Repository\UserRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['users:read']],
            security: "is_granted('ROLE_ADMIN')",
        ),
        new Get(
            security: "is_granted('ROLE_ADMIN')",
        ),
        new Post(
            uriTemplate: '/users/create',
            controller: UserCreateAction::class,
            input: SignUpRequestDto::class,
            name: 'signUp'
        ),
        new Put(
            controller: UserChangeDataAction::class,
            denormalizationContext: ['groups' => ['user:put:write']],
            security: "is_granted('ROLE_ADMIN')",
            input: UserChangeDataDto::class,
        ),
        new Delete(
            controller: DeleteAction::class,
            security: "is_granted('ROLE_ADMIN')",
        ),
        new Post(
            uriTemplate: 'users/about_me',
            controller: UserAboutMeAction::class,
            openapi: new Operation(
                summary: 'Shows info about the authenticated user'
            ),
            denormalizationContext: ['groups' => ['user:empty:body']],
            name: 'aboutMe',
        ),
        new Post(
            uriTemplate: 'users/auth',
            controller: UserAuthAction::class,
            openapi: new Operation(
                summary: 'Authorization'
            ),
            output: TokensDto::class,
            name: 'auth',
        ),
        new Post(
            uriTemplate: 'users/auth/refreshToken',
            controller: UserAuthByRefreshTokenAction::class,
            openapi: new Operation(
                summary: 'Authorization by refreshToken'
            ),
            input: RefreshTokenRequestDto::class,
            output: TokensDto::class,
            name: 'authByRefreshToken',
        ),
        new Post(
            uriTemplate: 'users/is_unique_email',
            controller: UserIsUniqueEmailAction::class,
            openapi: new Operation(
                summary: 'Checks email for uniqueness'
            ),
            denormalizationContext: ['groups' => ['user:isUniqueEmail:write']],
            name: 'isUniqueEmail',
        ),
        new Post(
            uriTemplate: 'users/request_reset_password',
            controller: UserRequestResetPasswordAction::class,
            openapi: new Operation(
                summary: 'Request for reset password'
            ),
            denormalizationContext: ['groups' => ['user:resetPassword:write']],
            name: 'requestResetPassword',
        ),
        new Post(
            uriTemplate: '/users/reset-password',
            controller: UserResetPasswordAction::class,
            denormalizationContext: ['groups' => ['user:resetPassword:write']],
            input: UserResetPasswordDto::class,
            name: 'resetPassword',
        ),
        new Put(
            uriTemplate: 'users/{id}/password',
            controller: UserChangePasswordAction::class,
            openapi: new Operation(
                summary: 'Changes password'
            ),
            denormalizationContext: ['groups' => ['user:changePassword:write']],
            security: "is_granted('ROLE_ADMIN')",
            input: UserChangePasswordDto::class,
            name: 'changePassword',
        ),

    ],
    normalizationContext: ['groups' => ['user:read', 'users:read']],
    denormalizationContext: ['groups' => ['user:write']],
    extraProperties: [
        'standard_put' => true,
    ],
)]
#[ApiFilter(OrderFilter::class, properties: ['id', 'createdAt', 'updatedAt', 'email'])]
#[ApiFilter(SearchFilter::class, properties: ['id' => 'exact', 'email' => 'partial'])]
#[UniqueEntity('email', message: 'This email is already used')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements
    UserInterface,
    CreatedAtSettableInterface,
    UpdatedAtSettableInterface,
    DeletedAtSettableInterface,
    PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['users:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\Email]
    #[Groups(['users:read', 'user:write', 'user:isUniqueEmail:write','user:resetPassword:write', 'certificate:forId:read', 'certificate:read', 'certificate:scanQr:read'])]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['user:write', 'user:changePassword:write'])]
    #[Assert\Length(min: 6, minMessage: 'Password must be at least {{ limit }} characters long')]
    private ?string $password = null;

    #[ORM\Column(type: 'array')]
    #[Groups(['user:read'])]
    private array $roles = [];

    #[ORM\Column(type: 'datetime')]
    #[Groups(['user:read'])]
    private ?DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['user:read'])]
    private ?DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $deletedAt = null;

    #[ORM\OneToMany(mappedBy: 'createdBy', targetEntity: Course::class)]
    private Collection $courses;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Certificate::class)]
    private Collection $certificates;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[Groups(['users:read', 'certificate:forId:read', 'certificate:read', 'certificate:scanQr:read'])]
    private ?Person $person = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: SecretKey::class)]
    private Collection $secretKeys;

    public function __construct()
    {
        $this->courses = new ArrayCollection();
        $this->certificates = new ArrayCollection();
        $this->secretKeys = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        return (string)$this->getId();
    }

    public function addRole(string $role): self
    {
        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function deleteRole(string $role): self
    {
        $roles = $this->roles;

        foreach ($roles as $roleKey => $roleName) {
            if ($roleName === $role) {
                unset($roles[$roleKey]);
                $this->setRoles($roles);
            }
        }

        return $this;
    }

    public function getSalt(): string
    {
        return '';
    }

    public function getUsername(): string
    {
        return $this->getEmail();
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDeletedAt(): ?DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @return Collection<int, Course>
     */
    public function getCourses(): Collection
    {
        return $this->courses;
    }

    public function addCourse(Course $course): self
    {
        if (!$this->courses->contains($course)) {
            $this->courses->add($course);
            $course->setCreatedBy($this);
        }

        return $this;
    }

    public function removeCourse(Course $course): self
    {
        if ($this->courses->removeElement($course)) {
            // set the owning side to null (unless already changed)
            if ($course->getCreatedBy() === $this) {
                $course->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Certificate>
     */
    public function getCertificates(): Collection
    {
        return $this->certificates;
    }

    public function addCertificate(Certificate $certificate): self
    {
        if (!$this->certificates->contains($certificate)) {
            $this->certificates->add($certificate);
            $certificate->setOwner($this);
        }

        return $this;
    }

    public function removeCertificate(Certificate $certificate): self
    {
        if ($this->certificates->removeElement($certificate)) {
            // set the owning side to null (unless already changed)
            if ($certificate->getOwner() === $this) {
                $certificate->setOwner(null);
            }
        }

        return $this;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(Person $person): self
    {
        // set the owning side of the relation if necessary
        if ($person->getUser() !== $this) {
            $person->setUser($this);
        }

        $this->person = $person;

        return $this;
    }

    public function getSecretKeys(): Collection
    {
        return $this->secretKeys;
    }

    public function addSecretKey(SecretKey $secretKey): self
    {
        if (!$this->secretKeys->contains($secretKey)) {
            $this->secretKeys->add($secretKey);
            $secretKey->setUser($this);
        }

        return $this;
    }

    public function removeSecretKey(SecretKey $secretKey): self
    {
        if ($this->secretKeys->removeElement($secretKey)) {
            if ($secretKey->getUser() === $this) {
                $secretKey->setUser(null);
            }
        }

        return $this;
    }
}
