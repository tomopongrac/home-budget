<?php

declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Dto\Authentication\LoginRequest;
use App\Dto\Authentication\LoginResponse;
use App\Exception\ApiWrongCredentialsException;
use App\Repository\UserRepository;
use App\Service\ValidatorService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraint;

class SecurityController extends AbstractController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserRepository $userRepository,
        private readonly JWTTokenManagerInterface $JWTTokenManager,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorService $validatorService,
    ) {
    }

    #[Route('/api/login', name: 'login', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $loginRequest = $this->serializer->deserialize($request->getContent(), LoginRequest::class, 'json', [
            'groups' => ['login:request'],
        ]);

        $this->validatorService->validate($loginRequest, [Constraint::DEFAULT_GROUP]);

        $user = $this->userRepository->findOneBy(['email' => $loginRequest->getEmail()]);

        if (null === $user) {
            throw new ApiWrongCredentialsException();
        }

        if (!$this->passwordHasher->isPasswordValid($user, $loginRequest->getPassword())) {
            throw new ApiWrongCredentialsException();
        }

        $loginResponse = (new LoginResponse())
            ->setToken($this->JWTTokenManager->create($user))
            ->setUser($user);

        return new JsonResponse($this->serializer->serialize($loginResponse, 'json', [
            'groups' => ['login:response'],
        ]), Response::HTTP_OK, [], true);
    }
}
