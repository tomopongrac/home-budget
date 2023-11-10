<?php

declare(strict_types=1);

namespace App\Tests\feature\Api\Authentication;

use App\Entity\User;
use App\Tests\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\ResetDatabase;

class RegistrationControllerTest extends ApiTestCase
{
    use ResetDatabase;

    /** @test */
    public function userCanRegister(): void
    {
        $requestData = [
            'email' => 'john.doe@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $json = $this->baseKernelBrowser()
            ->post('/api/register', [
                'json' => $requestData,
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_CREATED)
            ->json();

        $json->assertHas('status');

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager') ?? throw new \RuntimeException('The doctrine.orm.entity_manager service should exist.');
        $userRepository = $entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => $requestData['email']]);
        Assert::assertNotNull($user, 'The user should exist in the database.');

        Assert::assertNotEquals($requestData['password'], $user->getPassword(), 'The password should be hashed.');
    }

    /** @test */
    public function emailIsRequiredProperty(): void
    {
        $requestData = [
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $this->baseKernelBrowser()
            ->post('/api/register', [
                'json' => $requestData,
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function emailMustBeInValidFormat(): void
    {
        $requestData = [
            'email' => 'fake-email',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $this->baseKernelBrowser()
            ->post('/api/register', [
                'json' => $requestData,
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function passwordIsRequiredProperty(): void
    {
        $requestData = [
            'email' => 'john.doe@example.com',
            'password_confirmation' => 'password',
        ];

        $this->baseKernelBrowser()
            ->post('/api/register', [
                'json' => $requestData,
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function passwordConfirmationMustBeSameAsPassword(): void
    {
        $requestData = [
            'email' => 'john.doe@example.com',
            'password' => 'password',
            'password_confirmation' => 'different-password',
        ];

        $this->baseKernelBrowser()
            ->post('/api/register', [
                'json' => $requestData,
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
