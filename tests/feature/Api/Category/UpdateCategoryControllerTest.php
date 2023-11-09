<?php

declare(strict_types=1);

namespace App\Tests\feature\Api\Category;

use App\Entity\Category;
use App\Factory\CategoryFactory;
use App\Factory\UserFactory;
use App\Tests\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UpdateCategoryControllerTest extends ApiTestCase
{
    use ResetDatabase, Factories;

    /** @test */
    public function userMustBeAuthenticatedToUpdateCategory(): void
    {
        $category = CategoryFactory::createOne()->object();

        self::$client->request(
            'PUT',
            '/api/categories/' . $category->getId(),
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
    public function userCanUpdateHisCategory(): void
    {
        $user = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne(
            [
                'name' => 'Old Category name',
                'user' => $user,
            ]
        )->object();

        $this->authenticateUser($user);

        self::$client->request(
            'PUT',
            '/api/categories/' . $category->getId(),
            [],
            [],
            ['Content-Type' => 'application/json'],
            json_encode([
                'name' => 'New Category name',
            ], JSON_THROW_ON_ERROR)
        );

        Assert::assertEquals(Response::HTTP_OK, self::$client->getResponse()->getStatusCode());
        Assert::assertJsonStringEqualsJsonString(
            json_encode(
                [
                    'id' => $category->getId(),
                    'name' => 'New Category name',
                ],
                JSON_THROW_ON_ERROR
            ),
            self::$client->getResponse()->getContent()
        );
    }

    public function userCantUpdateCategoryFromOtherUser(): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::$client->getContainer()->get('doctrine.orm.entity_manager') ?? throw new \RuntimeException('The doctrine.orm.entity_manager service should exist.');
        $categoryRepository = $entityManager->getRepository(Category::class);

        $otherUser = UserFactory::createOne()->object();
        $user = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne(
            [
                'name' => 'Category name',
                'user' => $otherUser,
            ]
        )->object();

        $this->authenticateUser($user);
        self::$client->request(
            'PUT',
            '/api/categories/'.$category->getId(),
            [],
            [],
            ['Content-Type' => 'application/json'],
            json_encode([
                'name' => 'New Category name',
            ], JSON_THROW_ON_ERROR)
        );

        Assert::assertEquals(Response::HTTP_FORBIDDEN, self::$client->getResponse()->getStatusCode());

        // check that category is not updated
        $categoryInDB = $categoryRepository->find($category->getId());
        Assert::assertEquals($category->getName(), $categoryInDB->getName());
    }
}
