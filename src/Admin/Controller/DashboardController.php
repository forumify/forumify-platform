<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    public function __construct(
        #[Autowire(env: 'bool:FORUMIFY_HOSTED_INSTANCE')]
        private readonly bool $isHostedInstance,
    ) {
    }

    #[Route('/', name: 'dashboard')]
    public function __invoke(): Response
    {
        return $this->render('@Forumify/admin/dashboard/dashboard.html.twig', [
            'isCloudInstance' => $this->isHostedInstance,
        ]);
    }
}
