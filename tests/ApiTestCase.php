<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiTestCase extends WebTestCase
{
    protected static KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        self::$client = static::createClient();
    }

    protected function authenticateUser(User $user): void
    {
        $tokenManager = static::getContainer()->get(JWTTokenManagerInterface::class);
        $token = $tokenManager->create($user);
        self::$client->setServerParameter('HTTP_Authorization', 'Bearer '.$token);
    }
}
