<?php

declare(strict_types=1);

namespace App\Dto\Transaction;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

final class TransactionDataAggregationFilterParameters
{
    #[Groups(['transaction:data-aggregation'])]
    #[Assert\Date]
    private ?string $dateFrom = null;

    #[Groups(['transaction:data-aggregation'])]
    #[Assert\Date]
    private ?string $dateTo = null;

    #[Groups(['transaction:data-aggregation'])]
    private array $categories = [];

    public function getDateFrom(): ?string
    {
        return $this->dateFrom;
    }

    public function setDateFrom(?string $dateFrom): void
    {
        $this->dateFrom = $dateFrom;
    }

    public function getDateTo(): ?string
    {
        return $this->dateTo;
    }

    public function setDateTo(?string $dateTo): void
    {
        $this->dateTo = $dateTo;
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
