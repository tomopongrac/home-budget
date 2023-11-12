<?php

declare(strict_types=1);

namespace App\Controller\Api\Transaction;

use App\Entity\Transaction;
use App\Security\Voter\TransactionVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

class GetTransactionController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
    ) {
    }

    #[Route('/api/transactions/{id}', name: 'show_transaction', methods: ['GET'])]
    #[IsGranted(TransactionVoter::VIEW, 'transaction')]
    /**
     * @OA\Get(
     *     tags={"Transaction"},
     *     summary="Get a transaction"
     * )
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="Transaction id",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Transaction found",
     *     @Model(type=Transaction::class, groups={"transaction:read"})
     * )
     * @OA\Response(
     *    response=404,
     *     description="Transaction not found"
     * )
     * @OA\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     * @OA\Response(
     *     response=403,
     *     description="Access denied"
     * )
     */
    public function __invoke(Transaction $transaction): Response
    {
        return new JsonResponse($this->serializer->serialize($transaction, 'json', [
            'groups' => ['transaction:read'],
        ]), Response::HTTP_OK, [], true);
    }
}
