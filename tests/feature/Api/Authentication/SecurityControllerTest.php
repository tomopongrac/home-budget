<?php

declare(strict_types=1);

namespace App\Tests\feature\Api\Authentication;

use App\Factory\UserFactory;
use App\Tests\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class SecurityControllerTest extends ApiTestCase
{
    use ResetDatabase, Factories;

    /** @test */
    public function userCanLogin(): void
    {
        $user = UserFactory::createOne([
            'email' => 'john.doe@example.com',
        ]);

        $json = $this->baseKernelBrowser()
            ->post('/api/login', [
                'json' => [
                    'email' => $user->getEmail(),
                    'password' => 'password',
                ],
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->json();

        $json->assertHas('token')
            ->assertHas('user')
            ->assertMatches('user.email', 'john.doe@example.com');
    }

    /** @test */
    public function userCantLoginWithWrongCredentials(): void
    {
        UserFactory::createOne([
            'email' => 'john.doe@example.com',
            'password' => 'password',
        ]);

        $this->baseKernelBrowser()
            ->post('/api/login', [
                'json' => [
                    'email' => 'email@example.com',
                    'password' => 'fake-password',
                ],
            ])
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function emailIsRequiredPropertyInRequest(): void
    {
        $requestData = [
            'password' => 'password',
        ];

        $this->baseKernelBrowser()
            ->post('/api/login', [
                'json' => $requestData,
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function passwordIsRequiredPropertyInRequest(): void
    {
        $requestData = [
            'email' => 'john.doe@example.com',
        ];

        $this->baseKernelBrowser()
            ->post('/api/login', [
                'json' => $requestData,
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
