<?php

declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Entity\User;
use App\Service\ValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraint;

class RegisterController extends AbstractController
{

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorService $validatorService,
    ) {
    }

    #[Route('/api/register', name: 'register')]
    public function __invoke(Request $request): Response
    {
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json', [
            'groups' => ['user:write'],
        ]);

        $this->validatorService->validate($user, [Constraint::DEFAULT_GROUP]);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new Response('', Response::HTTP_CREATED);
    }
}
