<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Automation\Entity\Automation;
use Forumify\Automation\Repository\AutomationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/automation', 'automation_')]
class AutomationController extends AbstractController
{
    #[Route('s', 'list')]
    #[IsGranted('forumify.admin.settings.automations.view')]
    public function list(): Response
    {
        return $this->render('@Forumify/admin/automation/list.html.twig');
    }

    #[Route('/{id?<\d+>}', 'form')]
    #[IsGranted('forumify.admin.settings.automations.manage')]
    public function form(?Automation $automation = null): Response
    {
        return $this->render('@Forumify/admin/automation/automation.html.twig', [
            'automation' => $automation,
        ]);
    }

    #[Route('/{id<\d+>}/delete', 'delete')]
    #[IsGranted('forumify.admin.settings.automations.manage')]
    public function delete(
        AutomationRepository $automationRepository,
        Automation $automation,
        Request $request,
    ): Response {
        if (!$request->get('confirmed')) {
            return $this->render('@Forumify/admin/automation/delete.html.twig', [
                'automation' => $automation,
            ]);
        }

        $automationRepository->remove($automation);

        $this->addFlash('success', 'admin.automations.deleted');
        return $this->redirectToRoute('forumify_admin_automation_list');
    }
}
