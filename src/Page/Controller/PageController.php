<?php

declare(strict_types=1);

namespace Forumify\Page\Controller;

use Forumify\Core\Controller\IndexController;
use Forumify\Page\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PageController extends AbstractController
{
    #[Route('/{urlKey?}', 'page', requirements: ['urlKey' => '.*'], priority: -1)]
    public function __invoke(
        ?string $urlKey,
        Request $request,
        PageRepository $pageRepository,
    ): Response {
        $page = $pageRepository->findOneByUrlKey($urlKey);
        if ($page === null) {
            if (empty($urlKey)) {
                return $this->forward(IndexController::class);
            }
            throw $this->createNotFoundException("Page with url '$urlKey' not found.");
        }

        if ($page->getType() === 'html') {
            return $this->render($page->getUrlKey());
        }

        $pageHtml = $page;
        return $this->render('@Forumify/frontend/page_wrapper.html.twig', [
            'content' => $pageHtml,
        ]);
    }
}
