<?php

declare(strict_types=1);

namespace App\Tests\feature\Api\Category;

use App\Factory\CategoryFactory;
use App\Factory\UserFactory;
use App\Tests\ApiTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ShowCategoryControllerTest extends ApiTestCase
{
    use ResetDatabase, Factories;

    /** @test */
    public function userMustBeAuthenticatedToSeeCategory(): void
    {
        $category = CategoryFactory::createOne()->object();

        self::$client->request(
            'GET',
            '/api/categories/'.$category->getId(),
            [],
            [],
            ['Content-Type' => 'application/json'],
        );

        Assert::assertEquals(Response::HTTP_UNAUTHORIZED, self::$client->getResponse()->getStatusCode());
    }

    /** @test */
    public function userCanSeeHisCategory(): void
    {
        $user = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne(
            [
                'name' => 'Category name',
                'user' => $user,
            ]
        )->object();

        $this->authenticateUser($user);
        self::$client->request(
            'GET',
            '/api/categories/'.$category->getId(),
            [],
            [],
            ['Content-Type' => 'application/json'],
        );

        Assert::assertEquals(Response::HTTP_OK, self::$client->getResponse()->getStatusCode());
        Assert::assertJsonStringEqualsJsonString(
            json_encode(
                [
                    'id' => $category->getId(),
                    'name' => 'Category name',
                ],
                JSON_THROW_ON_ERROR
            ),
            self::$client->getResponse()->getContent()
        );
    }

    /** @test */
    public function userCantSeeCategoryFromOtherUser(): void
    {
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
            'GET',
            '/api/categories/'.$category->getId(),
            [],
            [],
            ['Content-Type' => 'application/json'],
        );

        Assert::assertEquals(Response::HTTP_FORBIDDEN, self::$client->getResponse()->getStatusCode());
    }
}
