<?php

declare(strict_types=1);

namespace App\Tests\feature\Api\Transaction;

use App\Entity\Category;
use App\Entity\Transaction;
use App\Enum\TransactionType;
use App\Factory\CategoryFactory;
use App\Factory\TransactionFactory;
use App\Factory\UserFactory;
use App\Tests\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UpdateTransactionControllerTest extends ApiTestCase
{
    use ResetDatabase, Factories;

    public const ENDPOINT_URI = '/api/transactions/%d';

    /** @test */
    public function userMustBeAuthenticatedToUpdateTransaction(): void
    {
        $user = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne(['user' => $user])->object();
        $transaction = TransactionFactory::createOne(['category' => $category])->object();

        $this->baseKernelBrowser()
            ->put(sprintf(self::ENDPOINT_URI, $transaction->getId()))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function userCanUpdateHisTransaction(): void
    {
        $user = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne(['user' => $user])->object();
        $newCategory = CategoryFactory::createOne(['user' => $user])->object();
        $transaction = TransactionFactory::createOne([
            'title' => 'Transaction title',
            'amount_cents' => 10_00,
            'category' => $category,
            'type' => TransactionType::INCOME,
            'active_at' => new \DateTime('2021-09-01'),
        ])->object();

        $json = $this->authenticateUserInBrowser($user)
            ->put(sprintf(self::ENDPOINT_URI, $transaction->getId()), [
                'json' => $this->getNewRequestData($newCategory),
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->json();

        $json->assertHas('id')
            ->assertMatches('id', $transaction->getId())
            ->assertHas('title')
            ->assertMatches('title', 'New Transaction title')
            ->assertHas('amount_cents')
            ->assertMatches('amount_cents', 20_00)
            ->assertHas('type')
            ->assertMatches('type', TransactionType::EXPENSE->value)
            ->assertHas('category')
            ->assertMatches('category.id', $newCategory->getId())
            ->assertHas('active_at')
            ->assertMatches('active_at', '2021-10-01');
    }

    /** @test */
    public function userCantUpdateCategoryFromOtherUser(): void
    {
        $user = UserFactory::createOne()->object();
        $otherUser = UserFactory::createOne()->object();
        $categoryForOtherUser = CategoryFactory::createOne(['user' => $otherUser])->object();
        $transactionForOtherUser = TransactionFactory::createOne(['category' => $categoryForOtherUser])->object();

        $this->authenticateUserInBrowser($user)
            ->put(sprintf(self::ENDPOINT_URI, $transactionForOtherUser->getId()), [
                'json' => $this->getNewRequestData($categoryForOtherUser),
            ])
            ->assertStatus(Response::HTTP_FORBIDDEN);

        // check that transaction is not updated
        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager') ?? throw new \RuntimeException('The doctrine.orm.entity_manager service should exist.');
        $transactionRepository = $entityManager->getRepository(Transaction::class);
        $transactionInDB = $transactionRepository->find($transactionForOtherUser->getId());
        Assert::assertEquals($transactionForOtherUser->getTitle(), $transactionInDB->getTitle());
    }

    protected function getNewRequestData(Category $newCategory): array
    {
        return [
            'title' => 'New Transaction title',
            'amount_cents' => 20_00,
            'categoryId' => $newCategory->getId(),
            'active_at' => '2021-10-01',
            'type' => TransactionType::EXPENSE->value,
        ];
    }
}
