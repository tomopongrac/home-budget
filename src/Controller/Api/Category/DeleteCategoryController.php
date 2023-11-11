<?php

declare(strict_types=1);

namespace App\Controller\Api\Category;

use App\Entity\Category;
use App\Security\Voter\CategoryVoter;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;
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
    /**
     * @OA\Delete(
     *     tags={"Category"},
     *     summary="Delete a category"
     * )
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="Category id",
     *
     *     @OA\Schema(type="integer")
     * )
     *
     * @OA\Response(
     *     response=204,
     *     description="Category deleted"
     * )
     * @OA\Response(
     *     response=403,
     *     description="Access denied"
     * )
     * @OA\Response(
     *     response=404,
     *     description="Category not found"
     * )
     * @OA\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     */
    public function __invoke(Category $category): Response
    {
        $this->entityManager->remove($category);
        $this->entityManager->flush();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
