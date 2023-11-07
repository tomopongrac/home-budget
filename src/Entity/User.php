<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\Traits\TimestampableTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use TimestampableTrait;

    public const ROLE_USER = 'ROLE_USER';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'email', type: Types::STRING, length: 180, unique: true, nullable: false)]
    #[Groups(['user:write'])]
    #[Assert\NotBlank()]
    #[Assert\Email()]
    #[Assert\Type(Types::STRING)]
    #[Assert\Length(max: 180)]
    private string $email;

    /**
     * @var array|string[]
     */
    #[ORM\Column(name: 'roles', type: Types::JSON)]
    private array $roles = [self::ROLE_USER];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(name: 'password', type: Types::STRING, length: 255, nullable: false)]
    #[Groups(['user:write'])]
    #[Assert\NotBlank()]
    #[Assert\Type(Types::STRING)]
    #[Assert\Length(min: 6, max: 255)]
    private string $password = '';

    #[Groups(['user:write'])]
    #[Assert\EqualTo(propertyPath: 'password')]
    private string $passwordConfirmation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        return array_unique($roles);
    }

    /**
     * @param array|string[] $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getPasswordConfirmation(): string
    {
        return $this->passwordConfirmation;
    }

    public function setPasswordConfirmation(string $passwordConfirmation): static
    {
        $this->passwordConfirmation = $passwordConfirmation;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
