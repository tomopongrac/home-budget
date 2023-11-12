<?php

declare(strict_types=1);

namespace App\Controller\Api\Transaction;

use App\Dto\Transaction\TransactionDataAggregationFilterParameters;
use App\Dto\Transaction\TransactionDataAggregationResponse;
use App\Entity\User;
use App\Repository\TransactionRepository;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class GetDataAggregationCollectionTransactionsController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly DenormalizerInterface $denormalizer,
        private readonly TransactionRepository $transactionRepository,
        private readonly Security $security,
        private readonly RequestStack $request,
    ) {
    }

    #[Route('/api/transactions/data-aggregation', name: 'data_aggregation_transaction', methods: ['GET'])]
    /**
     * @OA\Get(
     *     tags={"Transaction"},
     *     summary="Get data aggregation for transactions",
     *     security={{"Bearer":{}}}
     *     )
     *
     * @OA\Parameter(
     *     name="dateFrom",
     *     in="query",
     *     description="Date from",
     *     required=false,
     *
     *     @OA\Schema(type="string", format="date", example="2021-01-01")
     * )
     *
     * @OA\Parameter(
     *     name="dateTo",
     *     in="query",
     *     description="Date to",
     *     required=false,
     *
     *     @OA\Schema(type="string", format="date", example="2021-01-01")
     * )
     *
     * @OA\Parameter(
     *     name="categories",
     *     in="query",
     *     description="Categories Ids",
     *     required=false,
     *
     *     @OA\Schema(type="array", @OA\Items(type="integer", example="1,2"))
     * )
     *
     * @OA\Response (
     *     response=200,
     *     description="Success",
     *
     * @OA\JsonContent(
     *     type="object",
     *
     *     @OA\Property(property="date_from", type="string", format="date", example="2021-01-01"),
     *     @OA\Property(property="date_to", type="string", format="date", example="2021-01-01"),
     *     @OA\Property(property="total_income_cents", type="integer", example="1000"),
     *     @OA\Property(property="total_expense_cents", type="integer", example="1000"),
     *     @OA\Property(property="total_income_count", type="integer", example="10"),
     *     @OA\Property(property="total_expense_count", type="integer", example="10"),
     *    @OA\Property(property="total_balance_cents", type="integer", example="0")
     * )
     * )
     *
     * @OA\Response (
     *     response=401,
     *     description="Unauthorized"
     * )
     */
    public function __invoke(): Response
    {
        $queryParameters = $this->request->getCurrentRequest()?->query->all();

        /** @var TransactionDataAggregationFilterParameters $transactionDataAggregationFilterParameters */
        $transactionDataAggregationFilterParameters = $this->denormalizer->denormalize($queryParameters, TransactionDataAggregationFilterParameters::class, null, [
            'groups' => ['transaction:data-aggregation'],
        ]);

        /** @var User $user */
        $user = $this->security->getUser();

        /** @var TransactionDataAggregationResponse $data */
        $data = $this->transactionRepository->getTransactionDataAggregationFor($user, $transactionDataAggregationFilterParameters);

        return new JsonResponse($this->serializer->serialize($data, 'json', [
            'groups' => ['transaction:data-aggregation'],
            AbstractObjectNormalizer::SKIP_NULL_VALUES => false,
        ]), Response::HTTP_OK, [], true);
    }
}
