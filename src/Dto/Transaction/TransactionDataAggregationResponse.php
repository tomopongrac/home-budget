<?php

declare(strict_types=1);

namespace App\Dto\Transaction;

use Symfony\Component\Serializer\Annotation\Groups;

class TransactionDataAggregationResponse
{
    #[Groups(['transaction:data-aggregation'])]
    private ?string $dateFrom = null;

    #[Groups(['transaction:data-aggregation'])]
    private ?string $dateTo = null;

    #[Groups(['transaction:data-aggregation'])]
    private ?int $totalIncomeCents = null;

    #[Groups(['transaction:data-aggregation'])]
    private ?int $totalExpenseCents = null;

    #[Groups(['transaction:data-aggregation'])]
    private ?int $totalIncomeCount = null;

    #[Groups(['transaction:data-aggregation'])]
    private ?int $totalExpenseCount = null;

    #[Groups(['transaction:data-aggregation'])]
    private ?int $totalBalanceCents = null;

    public function getDateFrom(): ?string
    {
        return $this->dateFrom;
    }

    public function setDateFrom(?string $dateFrom): self
    {
        $this->dateFrom = $dateFrom;

        return $this;
    }

    public function getDateTo(): ?string
    {
        return $this->dateTo;
    }

    public function setDateTo(?string $dateTo): self
    {
        $this->dateTo = $dateTo;

        return $this;
    }

    public function getTotalIncomeCents(): ?int
    {
        return $this->totalIncomeCents;
    }

    public function setTotalIncomeCents(?int $totalIncomeCents): self
    {
        $this->totalIncomeCents = $totalIncomeCents;

        return $this;
    }

    public function getTotalExpenseCents(): ?int
    {
        return $this->totalExpenseCents;
    }

    public function setTotalExpenseCents(?int $totalExpenseCents): self
    {
        $this->totalExpenseCents = $totalExpenseCents;

        return $this;
    }

    public function getTotalIncomeCount(): ?int
    {
        return $this->totalIncomeCount;
    }

    public function setTotalIncomeCount(?int $totalIncomeCount): self
    {
        $this->totalIncomeCount = $totalIncomeCount;

        return $this;
    }

    public function getTotalExpenseCount(): ?int
    {
        return $this->totalExpenseCount;
    }

    public function setTotalExpenseCount(?int $totalExpenseCount): self
    {
        $this->totalExpenseCount = $totalExpenseCount;

        return $this;
    }

    public function getTotalBalanceCents(): ?int
    {
        return $this->totalBalanceCents;
    }

    public function setTotalBalanceCents(?int $totalBalanceCents): self
    {
        $this->totalBalanceCents = $totalBalanceCents;

        return $this;
    }
}
