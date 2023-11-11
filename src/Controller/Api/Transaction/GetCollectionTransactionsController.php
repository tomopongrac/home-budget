<?php

declare(strict_types=1);

namespace App\Controller\Api\Transaction;

use App\Entity\User;
use App\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class GetCollectionTransactionsController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly TransactionRepository $transactionRepository,
        private readonly Security $security,
    ) {
    }

    #[Route('/api/transactions', name: 'index_transaction', methods: ['GET'])]
    public function __invoke(): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        return new JsonResponse($this->serializer->serialize($this->transactionRepository->getAllUserTransactions($user), 'json', [
            'groups' => ['transaction:index'],
        ]), Response::HTTP_OK, [], true);
    }
}
