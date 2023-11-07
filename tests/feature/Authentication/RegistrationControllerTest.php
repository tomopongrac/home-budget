<?php

declare(strict_types=1);

namespace App\Tests\feature\Authentication;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\ResetDatabase;

class RegistrationControllerTest extends WebTestCase
{
    use ResetDatabase;

    /** @test */
    public function user_can_register(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager') ?? throw new \RuntimeException('The doctrine.orm.entity_manager service should exist.');
        $userRepository = $entityManager->getRepository(User::class);

        $requestData = [
            'email' => 'john.doe@example.com',
            'password' => 'password',
        ];
        $client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['Content-Type' => 'application/json'],
            json_encode($requestData, JSON_THROW_ON_ERROR))
        ;

        Assert::assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $user = $userRepository->findOneBy(['email' => $requestData['email']]);
        Assert::assertNotNull($user, 'The user should exist in the database.');
    }
}