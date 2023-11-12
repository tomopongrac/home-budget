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
}
