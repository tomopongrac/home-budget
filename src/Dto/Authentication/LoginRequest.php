<?php

declare(strict_types=1);

namespace App\Dto\Authentication;

use Symfony\Component\Serializer\Annotation\Groups;

final class LoginRequest
{
    #[Groups(['login:request'])]
    private string $email;

    #[Groups(['login:request'])]
    private string $password;

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }
}
