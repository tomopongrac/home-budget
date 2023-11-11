<?php

declare(strict_types=1);

namespace App\Controller\Api\Category;

use App\Entity\Category;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Security\Voter\CategoryVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

class GetCollectionCategoriesController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly CategoryRepository $categoryRepository,
        private readonly Security $security,
    ) {
    }

    #[Route('/api/categories', name: 'index_category', methods: ['GET'])]
    /**
     * @OA\Get(
     *     tags={"Category"},
     *     summary="Get all categories"
     * )
     * @OA\Response(
     *     response=200,
     *     description="Categories found",
     *     @OA\JsonContent(
     *         type="array",
     *         @OA\Items(ref=@Model(type=Category::class, groups={"category:index"}))
     *     )
     * )
     * @OA\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     */
    public function __invoke(): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        return new JsonResponse($this->serializer->serialize($this->categoryRepository->getAllUserCategories($user), 'json', [
            'groups' => ['category:index'],
        ]), Response::HTTP_OK, [], true);
    }
}
