<?php

declare(strict_types=1);

namespace App\Tests\feature\Api\Category;

use App\Factory\CategoryFactory;
use App\Factory\UserFactory;
use App\Tests\ApiTestCase;
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

        $this->baseKernelBrowser()
            ->get('/api/categories/'.$category->getId())
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
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

        $json = $this->authenticateUserInBrowser($user)
            ->get('/api/categories/'.$category->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->json();

        $json->assertHas('id')
            ->assertMatches('id', $category->getId())
            ->assertHas('name')
            ->assertMatches('name', 'Category name');
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

        $this->authenticateUserInBrowser($user)
            ->get('/api/categories/'.$category->getId())
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
