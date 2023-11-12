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

class GetDataAggregationCollectionTransactionsControllerTest extends ApiTestCase
{
    use ResetDatabase, Factories;

    public const ENDPOINT_URL = '/api/transactions/data-aggregation?%s';

    /** @test */
    public function userCanGetDataAggregationForAllTransaction(): void
    {
        $user = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne(['user' => $user])->object();
        $transaction1 = TransactionFactory::createOne([
            'amountCents' => 10_00,
            'type' => TransactionType::INCOME,
            'category' => $category,
        ])->object();
        $transaction2 = TransactionFactory::createOne([
            'amountCents' => 20_00,
            'type' => TransactionType::INCOME,
            'category' => $category,
        ])->object();
        $transaction3 = TransactionFactory::createOne([
            'amountCents' => 15_00,
            'type' => TransactionType::EXPENSE,
            'category' => $category,
        ])->object();
        $transaction4 = TransactionFactory::createOne([
            'amountCents' => 10_00,
            'type' => TransactionType::EXPENSE,
            'category' => $category,
        ])->object();

        $json = $this->authenticateUserInBrowser($user)
            ->get(sprintf(self::ENDPOINT_URL, 'minAmountCents=1500'))
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->json();

        $decodedJson = $json->decoded();

        $this->assertNull($decodedJson['date_from']);
        $this->assertNull($decodedJson['date_to']);
        $this->assertEquals(30_00, $decodedJson['total_income_cents']);
        $this->assertEquals(25_00, $decodedJson['total_expense_cents']);
        $this->assertEquals(5_00, $decodedJson['total_balance']);
        $this->assertEquals(2, $decodedJson['total_income_count']);
        $this->assertEquals(2, $decodedJson['total_expense_count']);
    }

    /** @test */
    public function userCanGetDataAggregationForAllTransactionInDataRange(): void
    {
        $user = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne(['user' => $user])->object();
        $transaction1 = TransactionFactory::createOne([
            'amountCents' => 10_00,
            'type' => TransactionType::INCOME,
            'category' => $category,
            'activeAt' => new \DateTimeImmutable('2022-01-01'),
        ])->object();
        $transaction2 = TransactionFactory::createOne([
            'amountCents' => 20_00,
            'type' => TransactionType::INCOME,
            'category' => $category,
            'activeAt' => new \DateTimeImmutable('2023-02-01'),
        ])->object();
        $transaction3 = TransactionFactory::createOne([
            'amountCents' => 15_00,
            'type' => TransactionType::EXPENSE,
            'category' => $category,
            'activeAt' => new \DateTimeImmutable('2022-02-01'),
        ])->object();
        $transaction4 = TransactionFactory::createOne([
            'amountCents' => 10_00,
            'type' => TransactionType::EXPENSE,
            'category' => $category,
            'activeAt' => new \DateTimeImmutable('2023-02-01'),
        ])->object();
        $transaction5 = TransactionFactory::createOne([
            'amountCents' => 10_00,
            'type' => TransactionType::EXPENSE,
            'category' => $category,
            'activeAt' => new \DateTimeImmutable('2023-05-01'),
        ])->object();

        $json = $this->authenticateUserInBrowser($user)
            ->get(sprintf(self::ENDPOINT_URL, 'dateFrom=2023-01-01&dateTo=2023-04-01'))
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->json();

        $decodedJson = $json->decoded();

        $this->assertEquals('2023-01-01', $decodedJson['date_from']);
        $this->assertEquals('2023-04-01', $decodedJson['date_to']);
        $this->assertEquals(20_00, $decodedJson['total_income_cents']);
        $this->assertEquals(10_00, $decodedJson['total_expense_cents']);
        $this->assertEquals(10_00, $decodedJson['total_balance']);
        $this->assertEquals(1, $decodedJson['total_income_count']);
        $this->assertEquals(1, $decodedJson['total_expense_count']);
    }

    /** @test */
    public function userCanGetDataAggregationForAllTransactionByCategories(): void
    {
        $user = UserFactory::createOne()->object();
        $categoryForFiltering = CategoryFactory::createOne(['user' => $user])->object();
        $categoryForFilteringSecond = CategoryFactory::createOne(['user' => $user])->object();
        $someOtherCategory = CategoryFactory::createOne(['user' => $user])->object();
        $transaction1 = TransactionFactory::createOne([
            'amountCents' => 10_00,
            'type' => TransactionType::INCOME,
            'category' => $someOtherCategory,
            'activeAt' => new \DateTimeImmutable('2022-01-01'),
        ])->object();
        $transaction2 = TransactionFactory::createOne([
            'amountCents' => 20_00,
            'type' => TransactionType::INCOME,
            'category' => $categoryForFiltering,
            'activeAt' => new \DateTimeImmutable('2023-02-01'),
        ])->object();
        $transaction3 = TransactionFactory::createOne([
            'amountCents' => 15_00,
            'type' => TransactionType::EXPENSE,
            'category' => $someOtherCategory,
            'activeAt' => new \DateTimeImmutable('2022-02-01'),
        ])->object();
        $transaction4 = TransactionFactory::createOne([
            'amountCents' => 10_00,
            'type' => TransactionType::EXPENSE,
            'category' => $categoryForFiltering,
            'activeAt' => new \DateTimeImmutable('2023-02-01'),
        ])->object();
        $transaction5 = TransactionFactory::createOne([
            'amountCents' => 10_00,
            'type' => TransactionType::EXPENSE,
            'category' => $someOtherCategory,
            'activeAt' => new \DateTimeImmutable('2023-05-01'),
        ])->object();
        $transaction6 = TransactionFactory::createOne([
            'amountCents' => 18_00,
            'type' => TransactionType::EXPENSE,
            'category' => $categoryForFilteringSecond,
            'activeAt' => new \DateTimeImmutable('2023-05-01'),
        ])->object();

        $json = $this->authenticateUserInBrowser($user)
            ->get(sprintf(self::ENDPOINT_URL, 'categories=' . $categoryForFiltering->getId().','.$categoryForFilteringSecond->getId()))
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->json();

        $decodedJson = $json->decoded();

        $this->assertNull($decodedJson['date_from']);
        $this->assertNull($decodedJson['date_to']);
        $this->assertEquals(20_00, $decodedJson['total_income_cents']);
        $this->assertEquals(28_00, $decodedJson['total_expense_cents']);
        $this->assertEquals(-8_00, $decodedJson['total_balance']);
        $this->assertEquals(1, $decodedJson['total_income_count']);
        $this->assertEquals(2, $decodedJson['total_expense_count']);
    }
}
