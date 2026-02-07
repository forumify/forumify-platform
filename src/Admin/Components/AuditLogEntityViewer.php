<?php

declare(strict_types=1);

namespace Forumify\Admin\Components;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('AuditLogEntityViewer', '@Forumify/admin/components/audit_log/entity_viewer.html.twig')]
#[IsGranted('forumify.admin.settings.audit_logs.view')]
class AuditLogEntityViewer
{
    use DefaultActionTrait;

    #[LiveProp]
    public string $class;

    #[LiveProp]
    public string $identifier;

    #[LiveProp(writable: true)]
    public bool $open = false;

    #[LiveAction]
    public function open(): void
    {
        $this->open = true;
    }

    #[LiveAction]
    public function close(): void
    {
        $this->open = false;
    }
}
