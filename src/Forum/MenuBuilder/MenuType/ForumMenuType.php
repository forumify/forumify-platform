<?php

declare(strict_types=1);

namespace Forumify\Forum\MenuBuilder\MenuType;

use Forumify\Core\Entity\MenuItem;
use Forumify\Core\MenuBuilder\MenuType\UrlMenuType;
use Forumify\Forum\MenuBuilder\Form\ForumPayloadType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ForumMenuType extends UrlMenuType
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function getType(): string
    {
        return 'forum';
    }

    public function getPayloadFormType(): ?string
    {
        return ForumPayloadType::class;
    }

    protected function getUrl(MenuItem $item): string
    {
        return $this->urlGenerator->generate('forumify_forum_forum', [
            'slug' => $item->getPayloadValue('forum'),
        ]);
    }
}
