<?php

declare(strict_types=1);

namespace App\Controller\Api\Transaction;

use App\Entity\Category;
use App\Entity\Transaction;
use App\Exception\ApiValidationException;
use App\Repository\CategoryRepository;
use App\Service\ValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraint;

class CreateTransactionController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager,
        private readonly CategoryRepository $categoryRepository,
        private readonly ValidatorService $validatorService,
    ) {
    }

    #[Route('/api/transactions', name: 'create_transaction', methods: ['POST'])]
    /**
     * @OA\Post(
     *     tags={"Transaction"},
     *     summary="Create a new transaction"
     * )
     *
     * @OA\RequestBody(
     *     required=true,
     *
     *     @Model(type=Transaction::class, groups={"transaction:write"})
     * )
     *
     * @OA\Response(
     *     response=201,
     *     description="Transaction created",
     *
     *     @Model(type=Transaction::class, groups={"transaction:read"})
     * )
     *
     * @OA\Response(
     *     response=422,
     *     description="Validation error",
     *
     *    @OA\JsonContent(
     *     type="object",
     *
     *     @OA\Property(property="status", type="string", example="Bad request"),
     *     @OA\Property(property="message", type="string", example="Validation error"),
     *     @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *   )
     * )
     *
     * @OA\Response(
     *     response=401,
     *     description="Unauthorized",
     *
     *     @OA\JsonContent(
     *     type="object",
     *
     *     @OA\Property(property="status", type="string", example="Unauthorized"),
     *     @OA\Property(property="message", type="string", example="JWT Token not found"),
     *     )
     * )
     */
    public function __invoke(Request $request): Response
    {
        $transaction = $this->serializer->deserialize($request->getContent(), Transaction::class, 'json', [
            'groups' => ['transaction:write'],
        ]);

        $this->validatorService->validate($transaction, [Constraint::DEFAULT_GROUP]);

        $category = $this->categoryRepository->find($transaction->getCategoryId());
        if (!$category instanceof Category) {
            throw new ApiValidationException(['Category not found']);
        }

        $transaction->setCategory($category);

        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

        return new JsonResponse($this->serializer->serialize($transaction, 'json', [
            'groups' => ['transaction:read'],
        ]), Response::HTTP_CREATED, [], true);
    }
}
