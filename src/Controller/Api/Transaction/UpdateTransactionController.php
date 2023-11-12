<?php

declare(strict_types=1);

namespace App\Controller\Api\Transaction;

use App\Entity\Category;
use App\Entity\Transaction;
use App\Exception\ApiValidationException;
use App\Repository\CategoryRepository;
use App\Security\Voter\TransactionVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

class UpdateTransactionController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager,
        private readonly CategoryRepository $categoryRepository,
    ) {
    }

    #[Route('/api/transactions/{id}', name: 'update_transaction', methods: ['PUT'])]
    #[IsGranted(TransactionVoter::EDIT, 'transaction')]
    public function __invoke(Transaction $transaction, Request $request): Response
    {
        $newTransaction = $this->serializer->deserialize($request->getContent(), Transaction::class, 'json', [
            'groups' => ['transaction:write'],
            'object_to_populate' => $transaction,
        ]);

        $category = $this->categoryRepository->find($transaction->getCategoryId());
        if (!$category instanceof Category) {
            throw new ApiValidationException(['Category not found']);
        }

        $transaction->setCategory($category);

        $this->entityManager->persist($newTransaction);
        $this->entityManager->flush();

        return new JsonResponse($this->serializer->serialize($newTransaction, 'json', [
            'groups' => ['transaction:read'],
        ]), Response::HTTP_OK, [], true);
    }
}
