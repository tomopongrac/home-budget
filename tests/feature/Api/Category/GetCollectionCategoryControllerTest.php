<?php

declare(strict_types=1);

namespace App\Tests\feature\Api\Category;

use App\Factory\CategoryFactory;
use App\Factory\UserFactory;
use App\Tests\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class GetCollectionCategoryControllerTest extends ApiTestCase
{
    use ResetDatabase, Factories;

    /** @test */
    public function userMustBeAuthenticatedToSeeCollectionCategories(): void
    {
        $category = CategoryFactory::createOne()->object();

        $this->baseKernelBrowser()
            ->get('/api/categories')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function userCanSeeHisCollectionCategories(): void
    {
        $user = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne(
            [
                'name' => 'Category name',
                'user' => $user,
            ]
        )->object();

        $json = $this->authenticateUserInBrowser($user)
            ->get('/api/categories')
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->json();

        $json->hasCount(1);
        $decodedJson = $json->decoded();
        $this->assertEquals($category->getId(), $decodedJson[0]['id']);
    }

    /** @test */
    public function userCantSeeCategoryFromOtherUser(): void
    {
        $otherUser = UserFactory::createOne()->object();
        $user = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne(
            [
                'name' => 'Category name',
                'user' => $user,
            ]
        )->object();
        $otherCategory = CategoryFactory::createOne(
            [
                'name' => 'Category name',
                'user' => $otherUser,
            ]
        )->object();

        $json = $this->authenticateUserInBrowser($user)
            ->get('/api/categories')
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->json();

        $json->hasCount(1);
        $decodedJson = $json->decoded();
        $this->assertEquals($category->getId(), $decodedJson[0]['id']);
    }
}
