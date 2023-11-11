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

class GetTransactionControllerTest extends ApiTestCase
{
    use ResetDatabase, Factories;

    public const ENDPOINT_URL = '/api/transactions/%d';

    /** @test */
    public function userMustBeAuthenticatedToSeeTransaction(): void
    {
        $user = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne(['user' => $user])->object();
        $transaction = TransactionFactory::createOne(['category' => $category])->object();

        $this->baseKernelBrowser()
            ->get(sprintf(self::ENDPOINT_URL, $transaction->getId()))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function userCanSeeHisTransaction(): void
    {
        $user = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne(['user' => $user])->object();
        $transaction = TransactionFactory::createOne([
            'title' => 'Transaction title',
            'category' => $category,
            'type' => TransactionType::INCOME,
        ])->object();

        $json = $this->authenticateUserInBrowser($user)
            ->get(sprintf(self::ENDPOINT_URL, $transaction->getId()))
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->json();

        $json->assertHas('id')
            ->assertMatches('id', $transaction->getId())
            ->assertHas('title')
            ->assertMatches('title', 'Transaction title')
            ->assertHas('type')
            ->assertMatches('type', TransactionType::INCOME->value)
            ->assertHas('category')
            ->assertMatches('category.id', $category->getId());
    }

    /** @test */
    public function userCantSeeTransactionFromOtherUser(): void
    {
        $user = UserFactory::createOne()->object();
        $otherUser = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne([
            'user' => $otherUser
        ])->object();
        $transaction = TransactionFactory::createOne([
            'category' => $category,
        ])->object();

        $this->authenticateUserInBrowser($user)
            ->get(sprintf(self::ENDPOINT_URL, $transaction->getId()))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
