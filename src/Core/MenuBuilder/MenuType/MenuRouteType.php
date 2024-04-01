<?php

declare(strict_types=1);

namespace Forumify\Core\MenuBuilder\MenuType;

use Forumify\Core\Entity\MenuItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MenuRouteType extends AbstractMenuType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function getType(): string
    {
        return 'route';
    }

    public function render(MenuItem $item): string
    {
        $payload = $item->getPayload();

        $label = htmlentities($item->getName());
        $url = $this->urlGenerator->generate($payload['route'], $payload['parameters']);

        return "<a class='btn-link' href='$url'>$label</a>";
    }
}
