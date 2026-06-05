<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['email'], message: 'This email is already registered')]
#[UniqueEntity(fields: ['pendingEmail'], message: 'This email is already registered')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const ROLE_ADMIN     = 'ROLE_ADMIN';
    public const ROLE_USER      = 'ROLE_USER';
    public const ROLE_MODERATOR = 'ROLE_MODERATOR';

    private const ALLOWED_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_USER,
        self::ROLE_MODERATOR,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    private ?string $email = null;

    #[ORM\Column(length: 180, nullable: true, unique: true)]
    private ?string $pendingEmail = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[Assert\NotBlank(groups: ['registration'])]
    #[Assert\PasswordStrength(
        message: 'Your password is too easy to guess. Requires using a stronger password.',
        groups: ['registration']
    )]
    #[Assert\NotCompromisedPassword(groups: ['registration'])]
    private ?string $plainPassword = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private ?string $firstName = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private ?string $lastName = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $passwordResetCode = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $passwordResetSentTime = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $confirmationCode = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $emailConfirmationSentTime = null;

    #[ORM\Column]
    private bool $emailConfirmed = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatar = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPendingEmail(): ?string
    {
        return $this->pendingEmail;
    }

    public function setPendingEmail(?string $pendingEmail): static
    {
        $this->pendingEmail = $pendingEmail;
        return $this;
    }

    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $hashedPassword): static
    {
        $this->password = $hashedPassword;
        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles   = $this->roles;
        $roles[] = self::ROLE_USER;

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        foreach ($roles as $role) {
            $this->assertRoleAllowed($role);
        }
        $this->roles = $roles;
        return $this;
    }

    public function addRole(string $role): static
    {
        $this->assertRoleAllowed($role);

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }
        return $this;
    }

    public function removeRole(string $role): static
    {
        $this->roles = array_values(
            array_filter($this->roles, fn($r) => $r !== $role)
        );
        return $this;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles(), true);
    }

    private function assertRoleAllowed(string $role): void
    {
        if (!in_array($role, self::ALLOWED_ROLES, true)) {
            throw new \InvalidArgumentException(
                sprintf('Role "%s" is not allowed. Allowed: %s', $role, implode(', ', self::ALLOWED_ROLES))
            );
        }
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function setPasswordResetCode(?string $hashedToken): static
    {
        $this->passwordResetCode = $hashedToken;
        return $this;
    }

    public function getPasswordResetCode(): ?string
    {
        return $this->passwordResetCode;
    }

    public function setConfirmationCode(?string $hashedToken): static
    {
        $this->confirmationCode = $hashedToken;
        return $this;
    }

    public function getPasswordResetSentTime(): ?\DateTimeImmutable
    {
        return $this->passwordResetSentTime;
    }

    public function setPasswordResetSentTime(?\DateTimeImmutable $time): static
    {
        $this->passwordResetSentTime = $time;
        return $this;
    }

    public function getConfirmationCode(): ?string
    {
        return $this->confirmationCode;
    }

    public function getEmailConfirmationSentTime(): ?\DateTimeImmutable
    {
        return $this->emailConfirmationSentTime;
    }

    public function setEmailConfirmationSentTime(?\DateTimeImmutable $time): static
    {
        $this->emailConfirmationSentTime = $time;
        return $this;
    }

    public function isEmailConfirmed(): bool
    {
        return $this->emailConfirmed;
    }

    public function setEmailConfirmed(bool $emailConfirmed): static
    {
        $this->emailConfirmed = $emailConfirmed;
        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(string $avatar): static
    {
        $this->avatar = $avatar;
        return $this;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
