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
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Entity\Transaction;

class GetCollectionTransactionsController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly TransactionRepository $transactionRepository,
        private readonly Security $security,
    ) {
    }

    #[Route('/api/transactions', name: 'index_transaction', methods: ['GET'])]
    /**
     * @OA\Get(
     *     tags={"Transaction"},
     *     summary="Get all transactions"
     * )
     *
     * @OA\Response(
     *     response=200,
     *     description="Transactions found",
     *
     *     @OA\JsonContent(
     *         type="array",
     *
     *         @OA\Items(ref=@Model(type=Transaction::class, groups={"transaction:index"}))
     *     )
     * )
     *
     * @OA\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     */
    public function __invoke(): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        return new JsonResponse($this->serializer->serialize($this->transactionRepository->getAllUserTransactions($user), 'json', [
            'groups' => ['transaction:index'],
        ]), Response::HTTP_OK, [], true);
    }
}
