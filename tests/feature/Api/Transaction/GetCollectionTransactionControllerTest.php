<?php

declare(strict_types=1);

namespace App\Tests\feature\Api\Transaction;

use App\Factory\CategoryFactory;
use App\Factory\TransactionFactory;
use App\Factory\UserFactory;
use App\Tests\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class GetCollectionTransactionControllerTest extends ApiTestCase
{
    use ResetDatabase, Factories;

    public const ENDPOINT_URL = '/api/transactions';

    /** @test */
    public function userMustBeAuthenticatedToSeeCollectionTransactions(): void
    {
        $user = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne(['user' => $user])->object();
        TransactionFactory::createOne(['category' => $category])->object();

        $this->baseKernelBrowser()
            ->get(self::ENDPOINT_URL)
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function userCanSeeHisCollectionTransactions(): void
    {
        $user = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne(['user' => $user])->object();
        $transaction = TransactionFactory::createOne(['category' => $category])->object();

        $json = $this->authenticateUserInBrowser($user)
            ->get(self::ENDPOINT_URL)
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->json();

        $json->hasCount(1);
        $decodedJson = $json->decoded();
        $this->assertEquals($transaction->getId(), $decodedJson[0]['id']);
    }

    /** @test */
    public function userCantSeeTransactionFromOtherUser(): void
    {
        $authenticatedUser = UserFactory::createOne()->object();
        $otherUser = UserFactory::createOne()->object();
        $categoryForOtherUser = CategoryFactory::createOne(['user' => $otherUser])->object();
        $categoryForAuthenticatedUser = CategoryFactory::createOne(['user' => $authenticatedUser])->object();
        $transactionFromOtherUser = TransactionFactory::createOne(['category' => $categoryForOtherUser])->object();
        $transactionForAuthenticatedUser = TransactionFactory::createOne(['category' => $categoryForAuthenticatedUser])->object();

        $json = $this->authenticateUserInBrowser($authenticatedUser)
            ->get(self::ENDPOINT_URL)
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->json();

        $json->hasCount(1);
        $decodedJson = $json->decoded();
        $this->assertEquals($transactionForAuthenticatedUser->getId(), $decodedJson[0]['id']);
        $this->assertEquals($categoryForAuthenticatedUser->getId(), $decodedJson[0]['category']['id']);
    }
}
