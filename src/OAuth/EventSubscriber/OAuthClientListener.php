<?php

declare(strict_types=1);

namespace Forumify\OAuth\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Forumify\OAuth\Entity\OAuthClient;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[AsEntityListener(Events::prePersist, 'enrichClient', entity: OAuthClient::class)]
class OAuthClientListener
{
    public function enrichClient(OAuthClient $client): void
    {
        if (empty($client->getClientId())) {
            $slugger = new AsciiSlugger();
            $clientId = $slugger->slug($client->getName())->lower()->toString();
            $client->setClientId($clientId);
        }

        if (empty($client->getClientSecret())) {
            $client->setClientSecret(md5(random_bytes(32)));
        }
    }
}
