<?php

declare(strict_types=1);

namespace Forumify\OAuth\Entity;

use DateInterval;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\User;
use Forumify\OAuth\Repository\OAuthAuthorizationCodeRepository;

#[ORM\Entity(repositoryClass: OAuthAuthorizationCodeRepository::class)]
class OAuthAuthorizationCode
{
    #[ORM\Id]
    #[ORM\Column(length: 255)]
    private string $code;

    #[ORM\ManyToOne(targetEntity: OAuthClient::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private OAuthClient $client;

    #[ORM\Column(type: 'text')]
    private string $scope;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'datetime')]
    private DateTime $validUntil;

    #[ORM\Column(length: 255)]
    private string $redirectUri;

    public function __construct()
    {
        $this->validUntil = (new DateTime())->add(new DateInterval('PT10M'));
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getClient(): OAuthClient
    {
        return $this->client;
    }

    public function setClient(OAuthClient $client): void
    {
        $this->client = $client;
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function setScope(string $scope): void
    {
        $this->scope = $scope;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getValidUntil(): DateTime
    {
        return $this->validUntil;
    }

    public function setValidUntil(DateTime $validUntil): void
    {
        $this->validUntil = $validUntil;
    }

    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

    public function setRedirectUri(string $redirectUri): void
    {
        $this->redirectUri = $redirectUri;
    }
}
