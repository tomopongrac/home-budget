<?php

declare(strict_types=1);

namespace App\Tests\feature\Api\Transaction;

use App\Enum\TransactionType;
use App\Factory\CategoryFactory;
use App\Factory\TransactionFactory;
use App\Factory\UserFactory;
use App\Tests\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class GetCollectionTransactionSearchControllerTest extends ApiTestCase
{
    use ResetDatabase, Factories;

    public const ENDPOINT_URL = '/api/transactions?%s';

    /** @test */
    public function userCanFilterTransactionByMinAmount(): void
    {
        $user = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne(['user' => $user])->object();
        $transaction1 = TransactionFactory::createOne([
            'category' => $category,
            'amountCents' => 1000,
        ])->object();
        $transaction2 = TransactionFactory::createOne([
            'category' => $category,
            'amountCents' => 2000,
        ])->object();

        $json = $this->authenticateUserInBrowser($user)
            ->get(sprintf(self::ENDPOINT_URL, 'minAmountCents=1500'))
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->json();

        $json->hasCount(1);
        $decodedJson = $json->decoded();
        $this->assertEquals($transaction2->getId(), $decodedJson[0]['id']);
    }

    /** @test */
    public function userCanFilterTransactionByMaxAmount(): void
    {
        $user = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne(['user' => $user])->object();
        $transaction1 = TransactionFactory::createOne([
            'category' => $category,
            'amountCents' => 1000,
        ])->object();
        $transaction2 = TransactionFactory::createOne([
            'category' => $category,
            'amountCents' => 2000,
        ])->object();

        $json = $this->authenticateUserInBrowser($user)
            ->get(sprintf(self::ENDPOINT_URL, 'maxAmountCents=1500'))
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->json();

        $json->hasCount(1);
        $decodedJson = $json->decoded();
        $this->assertEquals($transaction1->getId(), $decodedJson[0]['id']);
    }

    /** @test */
    public function userCanFilterTransactionByActiveDateFrom(): void
    {
        $user = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne(['user' => $user])->object();
        $transaction1 = TransactionFactory::createOne([
            'category' => $category,
            'activeAt' => new \DateTimeImmutable('2020-01-01'),
        ])->object();
        $transaction2 = TransactionFactory::createOne([
            'category' => $category,
            'activeAt' => new \DateTimeImmutable('2020-03-01'),
        ])->object();

        $json = $this->authenticateUserInBrowser($user)
            ->get(sprintf(self::ENDPOINT_URL, 'activeDateFrom=2020-02-01'))
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->json();

        $json->hasCount(1);
        $decodedJson = $json->decoded();
        $this->assertEquals($transaction2->getId(), $decodedJson[0]['id']);
    }

    /** @test */
    public function userCanFilterTransactionByActiveDateUntil(): void
    {
        $user = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne(['user' => $user])->object();
        $transaction1 = TransactionFactory::createOne([
            'category' => $category,
            'activeAt' => new \DateTimeImmutable('2020-01-01'),
        ])->object();
        $transaction2 = TransactionFactory::createOne([
            'category' => $category,
            'activeAt' => new \DateTimeImmutable('2020-03-01'),
        ])->object();

        $json = $this->authenticateUserInBrowser($user)
            ->get(sprintf(self::ENDPOINT_URL, 'activeDateUntil=2020-02-01'))
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->json();

        $json->hasCount(1);
        $decodedJson = $json->decoded();
        $this->assertEquals($transaction1->getId(), $decodedJson[0]['id']);
    }

    /** @test */
    public function userCanFilterTransactionByTransactionType(): void
    {
        $user = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne(['user' => $user])->object();
        $transaction1 = TransactionFactory::createOne([
            'category' => $category,
            'type' => TransactionType::INCOME,
        ])->object();
        $transaction2 = TransactionFactory::createOne([
            'category' => $category,
            'type' => TransactionType::EXPENSE,
        ])->object();

        $json = $this->authenticateUserInBrowser($user)
            ->get(sprintf(self::ENDPOINT_URL, 'transactionType=expense'))
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->json();

        $json->hasCount(1);
        $decodedJson = $json->decoded();
        $this->assertEquals($transaction2->getId(), $decodedJson[0]['id']);
    }

    /** @test */
    public function userCanFilterTransactionByHisCategories(): void
    {
        $user = UserFactory::createOne()->object();
        $category1 = CategoryFactory::createOne(['user' => $user])->object();
        $category2 = CategoryFactory::createOne(['user' => $user])->object();
        $category3 = CategoryFactory::createOne(['user' => $user])->object();
        $transaction1 = TransactionFactory::createOne([
            'category' => $category1,
        ])->object();
        $transaction2 = TransactionFactory::createOne([
            'category' => $category2,
        ])->object();
        $transaction3 = TransactionFactory::createOne([
            'category' => $category3,
        ])->object();

        $json = $this->authenticateUserInBrowser($user)
            ->get(sprintf(self::ENDPOINT_URL, 'categories='.$category1->getId().','.$category3->getId()))
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->json();

        $json->hasCount(2);
        $decodedJson = $json->decoded();
        $this->assertEquals($transaction1->getId(), $decodedJson[0]['id']);
        $this->assertEquals($transaction3->getId(), $decodedJson[1]['id']);
    }
    /** @test */
    public function userCantFilterTransactionByCategoriesOfOtherUser(): void
    {
        $user = UserFactory::createOne()->object();
        $otherUser = UserFactory::createOne()->object();
        $category1 = CategoryFactory::createOne(['user' => $user])->object();
        $category2 = CategoryFactory::createOne(['user' => $user])->object();
        $category3 = CategoryFactory::createOne(['user' => $otherUser])->object();
        $transaction1 = TransactionFactory::createOne([
            'category' => $category1,
        ])->object();
        $transaction2 = TransactionFactory::createOne([
            'category' => $category2,
        ])->object();
        $transaction3 = TransactionFactory::createOne([
            'category' => $category3,
        ])->object();

        $json = $this->authenticateUserInBrowser($user)
            ->get(sprintf(self::ENDPOINT_URL, 'categories='.$category1->getId().','.$category3->getId()))
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->json();

        $json->hasCount(1);
        $decodedJson = $json->decoded();
        $this->assertEquals($transaction1->getId(), $decodedJson[0]['id']);
    }
}
