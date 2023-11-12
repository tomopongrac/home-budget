<?php

declare(strict_types=1);

namespace App\Controller\Api\Transaction;

use App\Dto\Transaction\TransactionFilterParameters;
use App\Entity\Transaction;
use App\Entity\User;
use App\Repository\TransactionRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class GetCollectionTransactionsController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly DenormalizerInterface $denormalizer,
        private readonly TransactionRepository $transactionRepository,
        private readonly Security $security,
        private readonly RequestStack $request,
    ) {
    }

    #[Route('/api/transactions', name: 'index_transaction', methods: ['GET'])]
    /**
     * @OA\Get(
     *     tags={"Transaction"},
     *     summary="Get all transactions",
     *     security={{"Bearer":{}}}
     * )
     *
     * @OA\Parameter(
     *     name="minAmountCents",
     *     in="query",
     *     description="Minimum amount in cents",
     *     required=false,
     *
     *     @OA\Schema(type="integer")
     * )
     *
     * @OA\Parameter(
     *     name="maxAmountCents",
     *     in="query",
     *     description="Maximum amount in cents",
     *     required=false,
     *
     *     @OA\Schema(type="integer")
     * )
     *
     * @OA\Parameter(
     *     name="activeDateFrom",
     *     in="query",
     *     description="Active date from",
     *     required=false,
     *
     *     @OA\Schema(type="string", format="date", example="2021-01-01")
     * )
     *
     * @OA\Parameter(
     *     name="activeDateUntil",
     *     in="query",
     *     description="Active date until",
     *     required=false,
     *
     *     @OA\Schema(type="string", format="date", example="2021-01-01")
     * )
     *
     * @OA\Parameter(
     *     name="transactionType",
     *     in="query",
     *     description="Transaction type",
     *     required=false,
     *
     *     @OA\Schema(type="string", enum={"income", "expense"})
     * )
     *
     * @OA\Parameter(
     *     name="categories",
     *     in="query",
     *     description="Categories",
     *     required=false,
     *
     *     @OA\Schema(type="array", @OA\Items(type="string"))
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
        $queryParameters = $this->request->getCurrentRequest()?->query->all();

        $transactionFilterParameters = $this->denormalizer->denormalize($queryParameters, TransactionFilterParameters::class, null, [
            'groups' => ['transaction:filter'],
        ]);

        /** @var User $user */
        $user = $this->security->getUser();

        return new JsonResponse($this->serializer->serialize($this->transactionRepository->getAllUserTransactions($user, $transactionFilterParameters), 'json', [
            'groups' => ['transaction:index'],
        ]), Response::HTTP_OK, [], true);
    }
}
