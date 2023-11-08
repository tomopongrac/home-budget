<?php

declare(strict_types=1);

namespace App\Tests\feature\Authentication;

use App\Factory\UserFactory;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class SecurityControllerTest extends WebTestCase
{
    use ResetDatabase, Factories;

    /** @test */
    public function userCanLogin(): void
    {
        $user = UserFactory::createOne([
            'email' => 'john.doe@example.com',
        ]);

        static::ensureKernelShutdown();
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['Content-Type' => 'application/json'],
            json_encode([
                'email' => $user->getEmail(),
                'password' => 'password',
            ], JSON_THROW_ON_ERROR)
        );

        Assert::assertEquals(200, $client->getResponse()->getStatusCode());
        $responseContent = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        // check that the response contains a JWT token
        Assert::assertArrayHasKey('token', $responseContent);
        // check that the response contains the user data
        Assert::assertArrayHasKey('user', $responseContent);
        Assert::assertEquals('john.doe@example.com', $responseContent['user']['email']);
    }

    /** @test */
    public function userCantLoginWithWrongCredentials(): void
    {
        $user = UserFactory::createOne([
            'email' => 'john.doe@example.com',
            'password' => 'password',
        ]);

        static::ensureKernelShutdown();
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['Content-Type' => 'application/json'],
            json_encode([
                'email' => 'email@example.com',
                'password' => 'fake-password',
            ], JSON_THROW_ON_ERROR)
        );

        Assert::assertEquals(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());
    }

    /** @test */
    public function emailIsRequiredPropertyInRequest(): void
    {
        $client = static::createClient();

        $requestData = [
            'password' => 'password',
        ];

        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['Content-Type' => 'application/json'],
            json_encode($requestData, JSON_THROW_ON_ERROR))
        ;

        Assert::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());
    }

    /** @test */
    public function passwordIsRequiredPropertyInRequest(): void
    {
        $client = static::createClient();

        $requestData = [
            'email' => 'john.doe@example.com',
        ];

        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['Content-Type' => 'application/json'],
            json_encode($requestData, JSON_THROW_ON_ERROR))
        ;

        Assert::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());
    }
}
