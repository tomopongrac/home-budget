<?php

declare(strict_types=1);

namespace App\Controller\Api\Transaction;

use App\Entity\Category;
use App\Entity\Transaction;
use App\Exception\ApiValidationException;
use App\Repository\CategoryRepository;
use App\Service\ValidatorService;
use Doctrine\ORM\EntityManagerInterface;
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
