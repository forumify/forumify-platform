<?php

declare(strict_types=1);

namespace Forumify\Core\Controller;

use Forumify\Core\Compliance\ComplianceMode;
use Forumify\Core\Compliance\ComplianceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PrivacyPolicyController extends AbstractController
{
    public function __construct(
        private readonly ComplianceService $complianceService,
    ) {
    }

    #[Route('/privacy-policy', 'privacy_policy')]
    public function __invoke(): Response
    {
        $mode = $this->complianceService->getMode();
        if ($mode === ComplianceMode::Off) {
            throw $this->createNotFoundException();
        }

        if ($mode === ComplianceMode::Generated) {
            return $this->render('@Forumify/frontend/privacy_policy.html.twig');
        }

        $page = $this->complianceService->getCmsPage();
        if ($page === null) {
            throw $this->createNotFoundException();
        }

        return $this->redirectToRoute('forumify_cms_page', ['urlKey' => $page->getUrlKey()]);
    }
}
