<?php

declare(strict_types=1);

namespace App\Tests\feature\Api\Category;

use App\Entity\Category;
use App\Factory\UserFactory;
use App\Tests\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CreateCategoryControllerTest extends ApiTestCase
{
    use ResetDatabase, Factories;

    /** @test */
    public function userMustBeAuthenticatedToCreateCategory(): void
    {
        $this->baseKernelBrowser()
            ->post('/api/categories', [
                'json' => [
                    'name' => 'Category name',
                ],
            ])
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function userCanCreateCategory(): void
    {
        $user = UserFactory::createOne()->object();

        $json = $this->authenticateUserInBrowser($user)
            ->post('/api/categories', [
                'json' => [
                    'name' => 'Category name',
                ],
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_CREATED)
            ->json();

        $json->assertHas('id');

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager') ?? throw new \RuntimeException('The doctrine.orm.entity_manager service should exist.');
        $categoryRepository = $entityManager->getRepository(Category::class);
        $category = $categoryRepository->findOneBy(['name' => 'Category name']);
        Assert::assertNotNull($category, 'The category should exist in the database.');
        Assert::assertEquals($user->getId(), $category->getUser()->getId(), 'The category should belong to the user.');
    }

    /** @test */
    public function nameIsRequiredProperty(): void
    {
        $requestData = [];

        $user = UserFactory::createOne()->object();

        $this->authenticateUserInBrowser($user)
            ->post('/api/categories', [
                'json' => $requestData,
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
