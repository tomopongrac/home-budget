<?php

namespace App\Entity;

use App\Enum\TransactionType;
use App\Repository\TransactionRepository;
use App\Traits\TimestampableTrait;
use App\Validator as CustomAssert;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[ORM\Table(name: 'transaction')]
#[ORM\HasLifecycleCallbacks]
class Transaction
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['transaction:read', 'transaction:index'])]
    private ?int $id = null;

    #[ORM\Column(name: 'title', type: Types::STRING, length: 255, nullable: false)]
    #[Groups(['transaction:write', 'transaction:read', 'transaction:index'])]
    #[Assert\NotBlank()]
    private ?string $title = null;

    #[ORM\Column(name: 'amount_cents', type: Types::INTEGER, nullable: false)]
    #[Groups(['transaction:write', 'transaction:read', 'transaction:index'])]
    #[Assert\NotBlank()]
    #[Assert\Positive()]
    private ?int $amountCents = null;

    #[ORM\Column(name: 'active_at', type: Types::DATE_MUTABLE, nullable: false)]
    #[Groups(['transaction:write', 'transaction:read', 'transaction:index'])]
    #[Assert\NotBlank()]
    /**
     * @OA\Property(type="string", format="date", property="active_at", example="2021-01-01", description="When the transaction will be active")
     */
    private ?\DateTimeInterface $activeAt = null;

    #[ORM\Column(name: 'type', type: 'enum_transaction_type', nullable: false)]
    #[Groups(['transaction:write', 'transaction:read', 'transaction:index'])]
    #[Assert\NotBlank()]
    /**
     * @OA\Property(type="string", enum={"expense", "income"}, property="type", example="expense", description="Transaction type")
     */
    private TransactionType $type;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['transaction:read', 'transaction:index'])]
    private ?Category $category = null;

    #[Groups(['transaction:write'])]
    #[Assert\NotBlank()]
    #[CustomAssert\CategoryBelongsToAuthenticatedUser()]
    /**
     * @OA\Property(type="integer", property="category_id", example=1, description="Category ID")
     */
    private ?int $categoryId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getAmountCents(): ?int
    {
        return $this->amountCents;
    }

    public function setAmountCents(int $amountCents): static
    {
        $this->amountCents = $amountCents;

        return $this;
    }

    public function getType(): TransactionType
    {
        return $this->type;
    }

    public function setType(TransactionType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getActiveAt(): ?\DateTimeInterface
    {
        return $this->activeAt;
    }

    public function setActiveAt(\DateTimeInterface $activeAt): static
    {
        $this->activeAt = $activeAt;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }

    public function setCategoryId(?int $categoryId): static
    {
        $this->categoryId = $categoryId;

        return $this;
    }
}
