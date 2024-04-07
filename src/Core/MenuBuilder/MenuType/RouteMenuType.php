<?php

declare(strict_types=1);

namespace Forumify\Core\MenuBuilder\MenuType;

use Forumify\Core\Entity\MenuItem;
use Forumify\Core\MenuBuilder\Form\RoutePayloadType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RouteMenuType extends UrlMenuType
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

    protected function getUrl(MenuItem $item): string
    {
        $payload = $item->getPayload();

        return $this->urlGenerator->generate(
            $payload['route'] ?? 'forumify_core_index',
            $payload['parameters'] ?? []
        );
    }
}
