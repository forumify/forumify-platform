<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use Forumify\OAuth\Entity\OAuthClient;
use Gedmo\Tool\ActorProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

#[AsDecorator('stof_doctrine_extensions.tool.actor_provider')]
class BlameActorProvider implements ActorProviderInterface
{
    public function __construct(
        #[AutowireDecorated]
        private readonly ActorProviderInterface $decorated
    ) {
    }

    /**
     * @return object|string|null
     */
    public function getActor(): mixed
    {
        $user = $this->decorated->getActor();
        if ($user instanceof OAuthClient) {
            return $user->getUser();
        }

        return $user;
    }
}
