<?php


declare(strict_types=1);

namespace Forumify\Core\Form\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class NewUser
{
    #[Assert\Length(min: 4, max: 32, normalizer: 'trim')]
    #[Assert\Regex('/^[A-Za-z0-9-_]+$/', 'registration.validation_error.username_alphanumeric')]
    #[Assert\Regex('/[A-Za-z]/', 'registration.validation_error.username_one_letter')]
    private string $username;

    #[Assert\NotBlank]
    #[Assert\Email]
    private string $email;

    #[Assert\NotBlank]
    #[Assert\Length(min: 8)]
    private string $password;

    #[Assert\NotBlank]
    #[Assert\Timezone]
    private ?string $timezone = null;

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

    public function setPassword(#[\SensitiveParameter] string $password): void
    {
        $this->password = $password;
    }

    public function getTimezone(): string
    {
        return $this->timezone ?? 'UTC';
    }

    public function setTimezone(?string $timezone): void
    {
        $this->timezone = $timezone;
    }
}
