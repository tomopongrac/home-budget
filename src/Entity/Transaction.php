<?php

namespace App\Entity;

use App\Enum\TransactionType;
use App\Repository\TransactionRepository;
use App\Traits\TimestampableTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[ORM\Table(name: 'transaction')]
#[ORM\HasLifecycleCallbacks]
class Transaction
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'title', type: Types::STRING, length: 255, nullable: false)]
    private ?string $title = null;

    #[ORM\Column(name: 'amount_cents', type: Types::INTEGER, nullable: false)]
    private ?int $amountCents = null;

    #[ORM\Column(name: 'active_at', type: Types::DATETIME_MUTABLE, nullable: false)]
    private ?\DateTimeInterface $activeAt = null;

    #[ORM\Column(name: 'type', type: 'enum_transaction_type', nullable: false)]
    private TransactionType $type;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

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

    public function setActiveAt(\DateTimeImmutable $activeAt): static
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
}
