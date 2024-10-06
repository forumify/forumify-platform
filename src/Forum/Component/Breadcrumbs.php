<?php

declare(strict_types=1);

namespace Forumify\Forum\Component;

use Forumify\Core\Entity\HierarchicalInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'Breadcrumbs', template: '@Forumify/components/breadcrumbs.html.twig')]
class Breadcrumbs
{
    public ?HierarchicalInterface $entity = null;

    public function getEntries(): array
    {
        $entries = [];
        $current = $this->entity;
        while ($current !== null) {
            $entries[] = $current;
            $current = $current->getParent();
        }

        return array_reverse($entries);
    }
}
