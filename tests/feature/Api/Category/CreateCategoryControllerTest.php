<?php

declare(strict_types=1);

namespace App\Tests\feature\Api\Category;

use App\Entity\Category;
use App\Factory\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CreateCategoryControllerTest extends WebTestCase
{
    use ResetDatabase, Factories;

    private static KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        self::$client = static::createClient();
    }

    /** @test */
    public function userMustBeAuthenticatedToCreateCategory(): void
    {
        self::$client->request(
            'POST',
            '/api/categories',
            [],
            [],
            ['Content-Type' => 'application/json'],
            json_encode([
                'name' => 'Category name',
            ], JSON_THROW_ON_ERROR)
        );

        Assert::assertEquals(Response::HTTP_UNAUTHORIZED, self::$client->getResponse()->getStatusCode());
    }

    /** @test */
    public function userCanCreateCategory(): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::$client->getContainer()->get('doctrine.orm.entity_manager') ?? throw new \RuntimeException('The doctrine.orm.entity_manager service should exist.');
        $categoryRepository = $entityManager->getRepository(Category::class);

        $tokenManager = static::getContainer()->get(JWTTokenManagerInterface::class);
        $user = UserFactory::createOne()->object();
        $token = $tokenManager->create($user);
        self::$client->setServerParameter('HTTP_Authorization', 'Bearer ' . $token);

        self::$client->request(
            'POST',
            '/api/categories',
            [],
            [],
            ['Content-Type' => 'application/json'],
            json_encode([
                'name' => 'Category name',
            ], JSON_THROW_ON_ERROR)
        );

        Assert::assertEquals(Response::HTTP_CREATED, self::$client->getResponse()->getStatusCode());
        $category = $categoryRepository->findOneBy(['name' => 'Category name']);
        Assert::assertNotNull($category, 'The category should exist in the database.');
        Assert::assertEquals($user->getId(), $category->getUser()->getId(), 'The category should belong to the user.');

        Assert::assertEquals('Category name', json_decode(self::$client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR)['name']);
    }

    /** @test */
    public function nameIsRequiredProperty(): void
    {
        $requestData = [];

        $tokenManager = static::getContainer()->get(JWTTokenManagerInterface::class);
        $user = UserFactory::createOne()->object();
        $token = $tokenManager->create($user);
        self::$client->setServerParameter('HTTP_Authorization', 'Bearer ' . $token);

        self::$client->request(
            'POST',
            '/api/categories',
            [],
            [],
            ['Content-Type' => 'application/json'],
            json_encode($requestData, JSON_THROW_ON_ERROR)
        );

        Assert::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, self::$client->getResponse()->getStatusCode());
    }
}
