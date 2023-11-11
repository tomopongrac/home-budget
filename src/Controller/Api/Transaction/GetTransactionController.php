<?php

declare(strict_types=1);

namespace App\Controller\Api\Transaction;

use App\Entity\Category;
use App\Entity\Transaction;
use App\Security\Voter\CategoryVoter;
use App\Security\Voter\TransactionVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

class GetTransactionController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
    ) {
    }

    #[Route('/api/transactions/{id}', name: 'show_transaction', methods: ['GET'])]
    #[IsGranted(TransactionVoter::VIEW, 'transaction')]
    public function __invoke(Transaction $transaction): Response
    {
        return new JsonResponse($this->serializer->serialize($transaction, 'json', [
            'groups' => ['transaction:read'],
        ]), Response::HTTP_OK, [], true);
    }
}
