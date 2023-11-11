<?php

declare(strict_types=1);

namespace App\Controller\Api\Category;

use App\Entity\Category;
use App\Security\Voter\CategoryVoter;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

class GetCategoryController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
    ) {
    }

    #[Route('/api/categories/{id}', name: 'show_category', methods: ['GET'])]
    #[IsGranted(CategoryVoter::VIEW, 'category')]
    /**
     * @OA\Get(
     *     tags={"Category"},
     *     summary="Get a category"
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
     *     response=200,
     *     description="Category found",
     *
     *     @Model(type=Category::class, groups={"category:read"})
     * )
     *
     * @OA\Response(
     *     response=404,
     *     description="Category not found"
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
    public function __invoke(Category $category): Response
    {
        return new JsonResponse($this->serializer->serialize($category, 'json', [
            'groups' => ['category:read'],
        ]), Response::HTTP_OK, [], true);
    }
}
