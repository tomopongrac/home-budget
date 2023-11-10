<?php

declare(strict_types=1);

namespace App\Tests\feature\Api\Category;

use App\Entity\Category;
use App\Factory\CategoryFactory;
use App\Factory\UserFactory;
use App\Tests\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
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

        $this->baseKernelBrowser()
            ->delete('/api/categories/'.$category->getId())
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function userCanDeleteHisCategory(): void
    {
        $user = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne([
            'name' => 'Category name',
            'user' => $user,
        ])->object();

        $this->authenticateUserInBrowser($user)
            ->delete('/api/categories/'.$category->getId())
            ->assertStatus(Response::HTTP_NO_CONTENT);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager') ?? throw new \RuntimeException('The doctrine.orm.entity_manager service should exist.');
        $categoryRepository = $entityManager->getRepository(Category::class);
        $categoryInDB = $categoryRepository->find($category->getId());
        // check that category is deleted from database
        $this->assertNull($categoryInDB);
    }

    /** @test */
    public function userCantDeleteCategoryFromOtherUser(): void
    {
        $user = UserFactory::createOne()->object();
        $otherUser = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne([
            'name' => 'Category name',
            'user' => $otherUser,
        ])->object();

        $this->authenticateUserInBrowser($user)
            ->delete('/api/categories/'.$category->getId())
            ->assertStatus(Response::HTTP_FORBIDDEN);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager') ?? throw new \RuntimeException('The doctrine.orm.entity_manager service should exist.');
        $categoryRepository = $entityManager->getRepository(Category::class);
        $categoryInDB = $categoryRepository->find($category->getId());
        // check that category is not deleted from database
        $this->assertNotNull($categoryInDB);
        $this->assertEquals($category->getId(), $categoryInDB->getId());
    }
}
