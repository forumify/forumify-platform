<?php


declare(strict_types=1);

namespace Forumify\Core\Form\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class NewUser
{
    #[Assert\Length(min: 4, max: 32, normalizer: 'trim')]
    private string $username;

    #[Assert\Email]
    private string $email;

    #[Assert\Length(min: 8)]
    private string $password;

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

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
