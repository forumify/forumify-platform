<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Core\Entity\AuditLog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/audit-logs', 'audit_logs_')]
#[IsGranted('forumify.admin.settings.audit_logs.view')]
class AuditLogController extends AbstractController
{
    #[Route('', 'list')]
    public function list(): Response
    {
        return $this->render('@Forumify/admin/audit_log/list.html.twig');
    }

    #[Route('{uid:log}', 'view')]
    public function view(AuditLog $log): Response
    {
        return $this->render('@Forumify/admin/audit_log/view.html.twig', [
            'log' => $log,
        ]);
    }
}
