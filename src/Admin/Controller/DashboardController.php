<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('forumify.admin.dashboard.view')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function __invoke(): Response
    {
        return $this->render('@Forumify/admin/dashboard/dashboard.html.twig');
    }
}
