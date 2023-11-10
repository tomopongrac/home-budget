<?php

declare(strict_types=1);

namespace App\Tests\feature\Api\Category;

use App\Factory\CategoryFactory;
use App\Tests\ApiTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class DeleteCategoryControllerTest extends ApiTestCase
{
    use ResetDatabase, Factories;

    /** @test */
    public function userMustBeAuthenticatedToDeleteCategory(): void
    {
        $category = CategoryFactory::createOne()->object();

        self::$client->request(
            'DELETE',
            '/api/categories/'.$category->getId(),
            [],
            [],
            ['Content-Type' => 'application/json']
        );

        Assert::assertEquals(Response::HTTP_UNAUTHORIZED, self::$client->getResponse()->getStatusCode());
    }
}
