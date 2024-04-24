<?php

declare(strict_types=1);

namespace Forumify\Core\MenuBuilder\MenuType;

use Forumify\Core\Entity\MenuItem;
use Forumify\Core\MenuBuilder\MenuTypeInterface;
use Forumify\Core\Security\VoterAttribute;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractMenuType implements MenuTypeInterface
{
    private Security $security;

    #[Required]
    public function setSecurity(Security $security): void
    {
        $this->security = $security;
    }

    public function buildItem(MenuItem $item): string
    {
        if (!$this->canView($item)) {
            return '';
        }

        return $this->render($item);
    }

    public function getPayloadFormType(): ?string
    {
        return null;
    }

    protected function canView(MenuItem $item): bool
    {
        return $this->security->isGranted(VoterAttribute::ACL->value, [
            'permission' => 'view',
            'entity' => $item,
        ]);
    }

    abstract protected function render(MenuItem $item): string;
}
