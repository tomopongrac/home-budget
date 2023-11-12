<?php

declare(strict_types=1);

namespace App\Controller\Api\Category;

use App\Entity\Category;
use App\Entity\User;
use App\Service\ValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraint;

class CreateCategoryController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
        private readonly ValidatorService $validatorService,
    ) {
    }

    #[Route('/api/categories', name: 'create_category', methods: ['POST'])]
    /**
     * @OA\Post(
     *     tags={"Category"},
     *     summary="Create a new category"
     * )
     *
     * @OA\RequestBody(
     *     required=true,
     *
     *     @Model(type=Category::class, groups={"category:write"})
     * )
     *
     * @OA\Response(
     *     response=201,
     *     description="Category created",
     *
     *     @Model(type=Category::class, groups={"category:read"})
     * )
     *
     * @OA\Response(
     *     response=422,
     *     description="Validation error",
     *
     *     @OA\JsonContent(
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
     *     @OA\Property(property="message", type="string", example="JWT Token not found")
     *  )
     * )
     */
    public function __invoke(Request $request): Response
    {
        $category = $this->serializer->deserialize($request->getContent(), Category::class, 'json', [
            'groups' => ['category:write'],
        ]);

        $this->validatorService->validate($category, [Constraint::DEFAULT_GROUP]);

        /** @var User $user */
        $user = $this->security->getUser();
        $category->setUser($user);
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return new JsonResponse($this->serializer->serialize($category, 'json', [
            'groups' => ['category:read'],
        ]), Response::HTTP_CREATED, [], true);
    }
}
