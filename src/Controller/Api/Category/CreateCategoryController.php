<?php

declare(strict_types=1);

namespace App\Controller\Api\Category;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CreateCategoryController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
    ) {
    }

    #[Route('/api/categories', name: 'create_category', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $category = $this->serializer->deserialize($request->getContent(), Category::class, 'json', [
            'groups' => ['category:write'],
        ]);

        $category->setUser($this->security->getUser());
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return new Response('', Response::HTTP_CREATED);
    }
}
