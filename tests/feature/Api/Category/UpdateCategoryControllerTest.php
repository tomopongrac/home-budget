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

        $this->baseKernelBrowser()
            ->put('/api/categories/'.$category->getId())
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
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

        $json = $this->authenticateUserInBrowser($user)
            ->put('/api/categories/'.$category->getId(), [
                'json' => [
                    'name' => 'New Category name',
                ],
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->json();

        $json->assertHas('id')
            ->assertMatches('id', $category->getId())
            ->assertHas('name')
            ->assertMatches('name', 'New Category name');
    }

    /** @test */
    public function userCantUpdateCategoryFromOtherUser(): void
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
            ->put('/api/categories/'.$category->getId(), [
                'json' => [
                    'name' => 'New Category name',
                ],
            ])
            ->assertStatus(Response::HTTP_FORBIDDEN);

        // check that category is not updated
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager') ?? throw new \RuntimeException('The doctrine.orm.entity_manager service should exist.');
        $categoryRepository = $entityManager->getRepository(Category::class);
        $categoryInDB = $categoryRepository->find($category->getId());
        Assert::assertEquals($category->getName(), $categoryInDB->getName());
    }
}
