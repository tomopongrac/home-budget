<?php

declare(strict_types=1);

namespace App\Controller\Api\Transaction;

use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DeleteTransactionController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/api/transactions/{id}', name: 'delete_transaction', methods: ['DELETE'])]
    #[IsGranted('TRANSACTION_EDIT', 'transaction')]
    public function __invoke(Transaction $transaction): Response
    {
        $this->entityManager->remove($transaction);
        $this->entityManager->flush();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
