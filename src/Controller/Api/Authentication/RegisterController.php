<?php

declare(strict_types=1);

namespace App\Controller\Api\Authentication;

use App\Entity\User;
use App\Service\ImportDefaultCategoriesService;
use App\Service\ValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraint;

class RegisterController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorService $validatorService,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ImportDefaultCategoriesService $importDefaultCategoriesService,
    ) {
    }

    #[Route('/api/register', name: 'register', methods: ['POST'])]
    /**
     * @OA\Post(
     *     tags={"Authentication"},
     *     summary="Register a new user"
     * )
     *
     * @OA\RequestBody(
     *     required=true,
     *
     *     @Model(type=User::class, groups={"user:write"})
     * )
     *
     * @OA\Response(
     *     response=201,
     *     description="User created",
     *
     *     @OA\JsonContent(
     *     type="object",
     *
     *     @OA\Property(property="status", type="string", example="User created")
     *    )
     * )
     */
    public function __invoke(Request $request): Response
    {
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json', [
            'groups' => ['user:write'],
        ]);

        $this->validatorService->validate($user, [Constraint::DEFAULT_GROUP]);

        // Hash the password
        $plainPassword = $user->getPassword();
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // :TODO: Move this to an event listener or later to a message queue with some other things
        $this->importDefaultCategoriesService->importFor($user);

        return new JsonResponse(['status' => 'User created'], Response::HTTP_CREATED);
    }
}
