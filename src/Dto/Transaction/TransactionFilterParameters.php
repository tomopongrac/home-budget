<?php

declare(strict_types=1);

namespace App\Dto\Transaction;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

final class TransactionFilterParameters
{
    #[Groups(['transaction:filter'])]
    private ?string $minAmountCents = null;

    #[Groups(['transaction:filter'])]
    private ?string $maxAmountCents = null;

    #[Groups(['transaction:filter'])]
    #[Assert\Date]
    private ?string $activeDateFrom = null;

    #[Groups(['transaction:filter'])]
    #[Assert\Date]
    private ?string $activeDateUntil = null;

    #[Groups(['transaction:filter'])]
    private ?string $transactionType = null;

    #[Groups(['transaction:filter'])]
    private array $categories = [];

    public function getMinAmountCents(): ?string
    {
        return $this->minAmountCents;
    }

    public function setMinAmountCents(?string $minAmountCents): void
    {
        $this->minAmountCents = $minAmountCents;
    }

    public function getMaxAmountCents(): ?string
    {
        return $this->maxAmountCents;
    }

    public function setMaxAmountCents(?string $maxAmountCents): void
    {
        $this->maxAmountCents = $maxAmountCents;
    }

    public function getActiveDateFrom(): ?string
    {
        return $this->activeDateFrom;
    }

    public function setActiveDateFrom(?string $activeDateFrom): void
    {
        $this->activeDateFrom = $activeDateFrom;
    }

    public function getActiveDateUntil(): ?string
    {
        return $this->activeDateUntil;
    }

    public function setActiveDateUntil(?string $activeDateUntil): void
    {
        $this->activeDateUntil = $activeDateUntil;
    }

    public function getTransactionType(): ?string
    {
        return $this->transactionType;
    }

    public function setTransactionType(?string $transactionType): void
    {
        $this->transactionType = $transactionType;
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function setCategories(array $categories): void
    {
        $this->categories = $categories;
    }
}
