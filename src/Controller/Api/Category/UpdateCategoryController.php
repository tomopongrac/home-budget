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

class UpdateCategoryController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/api/categories/{id}', name: 'update_category', methods: ['PUT'])]
    #[IsGranted(CategoryVoter::EDIT, 'category')]
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
