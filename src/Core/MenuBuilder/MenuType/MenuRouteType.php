<?php

declare(strict_types=1);

namespace Forumify\Core\MenuBuilder\MenuType;

use Forumify\Core\Entity\MenuItem;
use Forumify\Core\MenuBuilder\Form\RoutePayloadType;
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

    public function getPayloadFormType(): ?string
    {
        return RoutePayloadType::class;
    }

    public function render(MenuItem $item): string
    {
        $payload = $item->getPayload();

        $label = htmlentities($item->getName());
        $url = $this->urlGenerator->generate(
            $payload['route'] ?? 'forumify_core_index',
            $payload['parameters'] ?? []
        );

        return "<a class='btn-link' href='$url'>$label</a>";
    }
}
