<?php

declare(strict_types=1);

namespace App\Tests\feature\Api\Transaction;

use App\Entity\Category;
use App\Entity\Transaction;
use App\Factory\CategoryFactory;
use App\Factory\UserFactory;
use App\Tests\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CreateTransactionControllerTest extends ApiTestCase
{
    use ResetDatabase, Factories;

    private const ENDPOINT_URL = '/api/transactions';

    /** @test */
    public function userMustBeAuthenticatedToCreateTransaction(): void
    {
        $this->baseKernelBrowser()
            ->post(self::ENDPOINT_URL, [
                'json' => [
                    'title' => 'Transaction title',
                    'amount' => 1000,
                    'categoryId' => 1,
                    'active_at' => '2021-01-01',
                ],
            ])
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function userCanCreateTransaction(): void
    {
        $user = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne(['user' => $user]);
        $requestData = $this->getRequestData($category);

        $json = $this->authenticateUserInBrowser($user)
            ->post(self::ENDPOINT_URL, [
                'json' => $requestData,
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_CREATED)
            ->json();

        $json->assertHas('id');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager') ?? throw new \RuntimeException('The doctrine.orm.entity_manager service should exist.');
        $transactionRepository = $entityManager->getRepository(Transaction::class);
        $transaction = $transactionRepository->findOneBy(['title' => $requestData['title']]);
        Assert::assertNotNull($transaction, 'The transaction should exist in the database.');
        Assert::assertEquals($category->getId(), $transaction->getCategory()->getId(), 'The category should belong to the transaction.');
    }

    /** @test */
    public function titleIsRequiredProperty(): void
    {
        $user = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne(['user' => $user]);
        $requestData = $this->getRequestData($category);
        unset($requestData['title']);

        $this->authenticateUserInBrowser($user)
            ->post(self::ENDPOINT_URL, [
                'json' => $requestData,
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function amountCentsIsRequiredProperty(): void
    {
        $user = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne(['user' => $user]);
        $requestData = $this->getRequestData($category);
        unset($requestData['amount_cents']);

        $this->authenticateUserInBrowser($user)
            ->post(self::ENDPOINT_URL, [
                'json' => $requestData,
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function categoryIdIsRequiredProperty(): void
    {
        $user = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne(['user' => $user]);
        $requestData = $this->getRequestData($category);
        unset($requestData['categoryId']);

        $this->authenticateUserInBrowser($user)
            ->post(self::ENDPOINT_URL, [
                'json' => $requestData,
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function categoryMustBelongsToAuthenticatedUser(): void
    {
        $user = UserFactory::createOne()->object();
        $otherUser = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne(['user' => $user]);
        $otherCategory = CategoryFactory::createOne(['user' => $otherUser]);
        $requestData = $this->getRequestData($otherCategory);

        $this->authenticateUserInBrowser($user)
            ->post(self::ENDPOINT_URL, [
                'json' => $requestData,
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function categoryMustExists(): void
    {
        $user = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne(['user' => $user]);
        $requestData = $this->getRequestData($category);
        $requestData['categoryId'] = 999;

        $this->authenticateUserInBrowser($user)
            ->post(self::ENDPOINT_URL, [
                'json' => $requestData,
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function activeAtIsRequiredProperty(): void
    {
        $user = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne(['user' => $user]);
        $requestData = $this->getRequestData($category);
        unset($requestData['active_at']);

        $this->authenticateUserInBrowser($user)
            ->post(self::ENDPOINT_URL, [
                'json' => $requestData,
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function typeIsRequiredProperty(): void
    {
        $user = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne(['user' => $user]);
        $requestData = $this->getRequestData($category);
        unset($requestData['type']);

        $this->authenticateUserInBrowser($user)
            ->post(self::ENDPOINT_URL, [
                'json' => $requestData,
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function activeAtRequiredProperty(): void
    {
        $user = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne(['user' => $user]);
        $requestData = $this->getRequestData($category);
        unset($requestData['active_at']);

        $this->authenticateUserInBrowser($user)
            ->post(self::ENDPOINT_URL, [
                'json' => $requestData,
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    protected function getRequestData(\Zenstruck\Foundry\Proxy|Category $category): array
    {
        $requestData = [
            'title' => 'Transaction title',
            'amount_cents' => 1000,
            'categoryId' => $category->getId(),
            'active_at' => '2021-01-01',
            'type' => 'expense',
        ];

        return $requestData;
    }
}
