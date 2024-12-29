<?php

declare(strict_types=1);

namespace Forumify\Cms\Controller\Frontend;

use DateTime;
use Forumify\Cms\Entity\Page;
use Forumify\Core\Controller\IndexController;
use Forumify\Cms\Repository\PageRepository;
use Forumify\Core\Security\VoterAttribute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

class PageController extends AbstractController
{
    public function __construct(private readonly Environment $twig)
    {
    }

    #[Route('/{urlKey?}', 'page', requirements: ['urlKey' => '.*'], priority: -250)]
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

        $this->denyAccessUnlessGranted(VoterAttribute::ACL->value, [
            'entity' => $page,
            'permission' => 'view',
        ]);

        return $this->render($page->getUrlKey(), [
            'page' => $page,
        ]);
    }

    #[Route('/page/{slug}/css', 'page_css')]
    public function css(Page $page, Request $request): Response
    {
        $lastModified = $page->getUpdatedAt() ?? $page->getCreatedAt();
        return $this->ifModifiedSince($request, $lastModified, $page->getCss(), [
            'Content-Type' => 'text/css',
        ]);
    }

    #[Route('/page/{slug}/javascript', 'page_js')]
    public function javascript(Page $page, Request $request): Response
    {
        $lastModified = $page->getUpdatedAt() ?? $page->getCreatedAt();
        return $this->ifModifiedSince($request, $lastModified, $page->getJavascript(), [
            'Content-Type' => 'text/javascript',
        ]);
    }

    private function ifModifiedSince(Request $request, DateTime $lastModified, ?string $content, array $headers = []): Response
    {
        $response = new Response();
        $response->setLastModified($lastModified);
        $response->setPublic();
        if ($response->isNotModified($request)) {
            return $response;
        }

        if (!empty($content)) {
            $content = $this->twig->createTemplate($content)->render();
        }

        $response->setContent($content);
        $response->headers->add($headers);
        return $response;
    }
}
