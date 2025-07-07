<?php

declare(strict_types=1);

namespace Forumify\OAuth\Entity;

use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\OAuth\Repository\OAuthClientRepository;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: OAuthClientRepository::class)]
class OAuthClient implements UserInterface
{
    use IdentifiableEntityTrait;

    /**
     * @var non-empty-string
     */
    #[ORM\Column(length: 255, unique: true)]
    private string $clientId;

    #[ORM\Column(length: 255)]
    private string $clientSecret;

    /**
     * @var array<string>
     */
    #[ORM\Column(type: 'simple_array')]
    private array $redirectUris = [];

    /**
     * @return non-empty-string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @param non-empty-string $clientId
     */
    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
    }

    public function getUserIdentifier(): string
    {
        return $this->getClientId();
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    public function setClientSecret(string $clientSecret): void
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * @return array<string>
     */
    public function getRedirectUris(): array
    {
        return $this->redirectUris;
    }

    /**
     * @param array<string> $redirectUris
     */
    public function setRedirectUris(array $redirectUris): void
    {
        $this->redirectUris = $redirectUris;
    }

    /** @inheritDoc */
    public function getRoles(): array
    {
        return ['ROLE_USER', 'ROLE_OAUTH_CLIENT'];
    }

    public function eraseCredentials(): void
    {
    }
}
