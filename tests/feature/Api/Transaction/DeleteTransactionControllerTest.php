<?php

declare(strict_types=1);

namespace App\Tests\feature\Api\Transaction;

use App\Entity\Transaction;
use App\Factory\CategoryFactory;
use App\Factory\TransactionFactory;
use App\Factory\UserFactory;
use App\Tests\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class DeleteTransactionControllerTest extends ApiTestCase
{
    use ResetDatabase, Factories;

    private const ENDPOINT_URL = '/api/transactions/%d';

    /** @test */
    public function userMustBeAuthenticatedToDeleteTransaction(): void
    {
        $user = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne(['user' => $user])->object();
        $transaction = TransactionFactory::createOne(['category' => $category])->object();

        $this->baseKernelBrowser()
            ->delete(sprintf(self::ENDPOINT_URL, $transaction->getId()))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function userCanDeleteHisTransaction(): void
    {
        $user = UserFactory::createOne()->object();
        $category = CategoryFactory::createOne(['user' => $user])->object();
        $transaction = TransactionFactory::createOne(['category' => $category])->object();

        $this->authenticateUserInBrowser($user)
            ->delete(sprintf(self::ENDPOINT_URL, $transaction->getId()))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager') ?? throw new \RuntimeException('The doctrine.orm.entity_manager service should exist.');
        $transactionRepository = $entityManager->getRepository(Transaction::class);
        $transactionInDB = $transactionRepository->find($transaction->getId());
        // check that transaction is deleted from database
        $this->assertNull($transactionInDB);
    }

    /** @test */
    public function userCantDeleteTransactionFromOtherUser(): void
    {
        $user = UserFactory::createOne()->object();
        $otherUser = UserFactory::createOne()->object();
        $categoryFromOtherUser = CategoryFactory::createOne(['user' => $otherUser])->object();
        $transaction = TransactionFactory::createOne(['category' => $categoryFromOtherUser])->object();

        $this->authenticateUserInBrowser($user)
            ->delete(sprintf(self::ENDPOINT_URL, $transaction->getId()))
            ->assertStatus(Response::HTTP_FORBIDDEN);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager') ?? throw new \RuntimeException('The doctrine.orm.entity_manager service should exist.');
        $transactionRepository = $entityManager->getRepository(Transaction::class);
        $transactionInDB = $transactionRepository->find($transaction->getId());
        // check that transaction is deleted from database
        $this->assertNotNull($transactionInDB);
        $this->assertEquals($transaction->getId(), $transactionInDB->getId());
    }
}
