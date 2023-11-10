<?php

declare(strict_types=1);

namespace App\Controller\Api\Category;

use App\Entity\Category;
use App\Security\Voter\CategoryVoter;
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
    public function __invoke(Category $category): Response
    {
        return new JsonResponse($this->serializer->serialize($category, 'json', [
            'groups' => ['category:read'],
        ]), Response::HTTP_OK, [], true);
    }
}
