<?php

namespace App\Repository;

use App\Dto\Transaction\TransactionDataAggregationFilterParameters;
use App\Dto\Transaction\TransactionDataAggregationResponse;
use App\Dto\Transaction\TransactionFilterParameters;
use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 *
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function getAllUserTransactions(User $user, TransactionFilterParameters $filterParameters): mixed
    {
        $q = $this->createQueryBuilder('t')
            ->join('t.category', 'c')
            ->andWhere('c.user = :user')
            ->setParameter('user', $user);

        $this->applyFilterParameters($filterParameters, $q);

        return $q
            ->getQuery()
            ->getResult()
        ;
    }

    public function getTransactionDataAggregationFor(User $user, TransactionDataAggregationFilterParameters $filterParameters): mixed
    {
        $q = $this->createQueryBuilder('t')
            ->select('SUM(CASE WHEN t.type = \'income\' THEN t.amountCents ELSE 0 END) AS totalIncome')
            ->addSelect('SUM(CASE WHEN t.type = \'expense\' THEN t.amountCents ELSE 0 END) AS totalExpenses')
            ->addSelect('SUM(CASE WHEN t.type = \'income\' THEN 1 ELSE 0 END) AS countIncome')
            ->addSelect('SUM(CASE WHEN t.type = \'expense\' THEN 1 ELSE 0 END) AS countExpenses')
            ->addSelect('SUM(CASE WHEN t.type = \'income\' THEN t.amountCents ELSE -t.amountCents	 END) AS balance')
            ->join('t.category', 'c')
            ->andWhere('c.user = :user')
            ->setParameter('user', $user);

        $this->applyFilteringForDataAggregation($filterParameters, $q);

        /** @var array $result */
        $result = $q
            ->getQuery()
            ->getSingleResult();

        $transationDataAggregation = (new TransactionDataAggregationResponse())
            ->setDateFrom($filterParameters->getDateFrom())
            ->setDateTo($filterParameters->getDateTo())
            ->setTotalIncomeCents($result['totalIncome'])
            ->setTotalExpenseCents($result['totalExpenses'])
            ->setTotalIncomeCount($result['countIncome'])
            ->setTotalExpenseCount($result['countExpenses'])
            ->setTotalBalanceCents($result['balance']);

        return $transationDataAggregation;
    }

    private function applyFilterParameters(TransactionFilterParameters $filterParameters, \Doctrine\ORM\QueryBuilder $q): void
    {
        if (null !== $filterParameters->getMinAmountCents()) {
            $q
                ->andWhere('t.amountCents >= :minAmountCents')
                ->setParameter('minAmountCents', $filterParameters->getMinAmountCents());
        }

        if (null !== $filterParameters->getMaxAmountCents()) {
            $q
                ->andWhere('t.amountCents <= :maxAmountCents')
                ->setParameter('maxAmountCents', $filterParameters->getMaxAmountCents());
        }

        if (null !== $filterParameters->getActiveDateFrom()) {
            $q
                ->andWhere('t.activeAt >= :activeDateFrom')
                ->setParameter('activeDateFrom', $filterParameters->getActiveDateFrom());
        }

        if (null !== $filterParameters->getActiveDateUntil()) {
            $q
                ->andWhere('t.activeAt <= :activeDateUntil')
                ->setParameter('activeDateUntil', $filterParameters->getActiveDateUntil());
        }

        if (null !== $filterParameters->getTransactionType()) {
            $q
                ->andWhere('t.type = :transactionType')
                ->setParameter('transactionType', $filterParameters->getTransactionType());
        }

        if (0 !== count($filterParameters->getCategories())) {
            $q
                ->andWhere('c.id IN (:categories)')
                ->setParameter('categories', $filterParameters->getCategories());
        }
    }

    private function applyFilteringForDataAggregation(
        TransactionDataAggregationFilterParameters $filterParameters,
        \Doctrine\ORM\QueryBuilder $q
    ): void {
        if (null !== $filterParameters->getDateFrom()) {
            $q
                ->andWhere('t.activeAt >= :activeDateFrom')
                ->setParameter('activeDateFrom', $filterParameters->getDateFrom());
        }

        if (null !== $filterParameters->getDateTo()) {
            $q
                ->andWhere('t.activeAt <= :activeDateUntil')
                ->setParameter('activeDateUntil', $filterParameters->getDateTo());
        }

        if (0 !== count($filterParameters->getCategories())) {
            $q
                ->andWhere('c.id IN (:categories)')
                ->setParameter('categories', $filterParameters->getCategories());
        }
    }
}
