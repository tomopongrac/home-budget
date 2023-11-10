<?php

declare(strict_types=1);

namespace App\Controller\Api\Category;

use App\Entity\Category;
use App\Security\Voter\CategoryVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DeleteCategoryController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/api/categories/{id}', name: 'delete_category', methods: ['DELETE'])]
    #[IsGranted(CategoryVoter::EDIT, 'category')]
    public function __invoke(Category $category): Response
    {
        $this->entityManager->remove($category);
        $this->entityManager->flush();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
