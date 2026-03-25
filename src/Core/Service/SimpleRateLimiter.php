<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use Forumify\Core\Security\VoterAttribute;
use LogicException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

#[Autoconfigure(shared: false)]
class SimpleRateLimiter
{
    private ?RateLimiterFactoryInterface $limiter = null;

    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function setLimiter(RateLimiterFactoryInterface $limiter): void
    {
        $this->limiter = $limiter;
    }

    public function limit(): void
    {
        if ($this->limiter === null) {
            throw new LogicException('Calling "limit" before setting a rate limiter is not possible.');
        }

        $user = $this->security->getUser();
        if ($user === null) {
            return;
        }

        if ($this->security->isGranted(VoterAttribute::SuperAdmin->value)) {
            return;
        }

        $this->limiter->create($user->getUserIdentifier())->consume()->ensureAccepted();
    }
}
