<?php

declare(strict_types=1);

namespace App\Controller\Authentication;

use App\Exception\ApiWrongCredentialsException;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserRepository $userRepository,
        private readonly JWTTokenManagerInterface $JWTTokenManager,
    ) {
    }

    #[Route('/api/login', name: 'login', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $decodedRequest = json_decode($request->getContent(), true);
        $user = $this->userRepository->findOneBy(['email' => $decodedRequest['email']]);

        if (!$user) {
            throw new ApiWrongCredentialsException();
        }

        if (!$this->passwordHasher->isPasswordValid($user, $decodedRequest['password'])) {
            throw new ApiWrongCredentialsException();
        }

        return new JsonResponse(['token' => $this->JWTTokenManager->create($user), 'user' => $user]);
    }
}
