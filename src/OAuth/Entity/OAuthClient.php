<?php

declare(strict_types=1);

namespace Forumify\OAuth\Entity;

use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\OAuth\Repository\OAuthClientRepository;

#[ORM\Entity(repositoryClass: OAuthClientRepository::class)]
class OAuthClient
{
    use IdentifiableEntityTrait;

    #[ORM\Column(unique: true)]
    private string $clientId;

    #[ORM\Column]
    private string $clientSecret;

    #[ORM\Column(type: 'simple_array')]
    private array $redirectUris = [];

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    public function setClientSecret(string $clientSecret): void
    {
        $this->clientSecret = $clientSecret;
    }

    public function getRedirectUris(): array
    {
        return $this->redirectUris;
    }

    public function setRedirectUris(array $redirectUris): void
    {
        $this->redirectUris = $redirectUris;
    }
}
