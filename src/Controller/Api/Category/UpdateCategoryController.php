<?php

declare(strict_types=1);

namespace App\Controller\Api\Category;

use App\Entity\Category;
use App\Security\Voter\CategoryVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

class UpdateCategoryController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/api/categories/{id}', name: 'update_category', methods: ['PUT'])]
    #[IsGranted(CategoryVoter::EDIT, 'category')]
    /**
     * @OA\Put(
     *     tags={"Category"},
     *     summary="Update a category"
     * )
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="Category id",
     *     @OA\Schema(type="integer")
     * )
     * @OA\RequestBody(
     *     @Model(type=Category::class, groups={"category:write"})
     * )
     * @OA\Response(
     *     response=200,
     *     description="Category updated",
     *     @Model(type=Category::class, groups={"category:read"})
     * )
     * @OA\Response(
     *     response=403,
     *     description="Access denied"
     * )
     * @OA\Response(
     *     response=404,
     *     description="Category not found"
     * )
     *  @OA\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     */
    public function __invoke(Category $category, Request $request): Response
    {
        $newCategory = $this->serializer->deserialize($request->getContent(), Category::class, 'json', [
            'groups' => ['category:write'],
            'object_to_populate' => $category,
        ]);

        $this->entityManager->persist($newCategory);
        $this->entityManager->flush();

        return new JsonResponse($this->serializer->serialize($newCategory, 'json', [
            'groups' => ['category:read'],
        ]), Response::HTTP_OK, [], true);
    }
}
