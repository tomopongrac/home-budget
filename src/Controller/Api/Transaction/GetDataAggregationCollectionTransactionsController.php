<?php

declare(strict_types=1);

namespace App\Controller\Api\Transaction;

use App\Dto\Transaction\TransactionDataAggregationFilterParameters;
use App\Dto\Transaction\TransactionDataAggregationResponse;
use App\Entity\User;
use App\Repository\TransactionRepository;
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
