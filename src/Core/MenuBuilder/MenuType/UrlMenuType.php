<?php

declare(strict_types=1);

namespace Forumify\Core\MenuBuilder\MenuType;

use Forumify\Core\Entity\MenuItem;
use Forumify\Core\MenuBuilder\Form\UrlPayloadType;
use Symfony\Contracts\Service\Attribute\Required;
use Twig\Environment;

class UrlMenuType extends AbstractMenuType
{
    protected Environment $twig;

    #[Required]
    public function setTwig(Environment $twig): void
    {
        $this->twig = $twig;
    }

    public function getType(): string
    {
        return 'url';
    }

    public function getPayloadFormType(): ?string
    {
        return UrlPayloadType::class;
    }

    public function render(MenuItem $item): string
    {
        $url = $this->getUrl($item);
        return $this->twig->render('@Forumify/frontend/menu/url.html.twig', [
            'url' => $url,
            'label' => $item->getName(),
            'external' => $item->getPayloadValue('external')
        ]);
    }

    protected function getUrl(MenuItem $item): string
    {
        return $item->getPayloadValue('url') ?? '';
    }
}
