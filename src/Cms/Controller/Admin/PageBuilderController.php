<?php

declare(strict_types=1);

namespace Forumify\Cms\Controller\Admin;

use Forumify\Cms\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Twig\Environment;

#[Route('pagebuilder', 'page_builder_')]
#[IsGranted('forumify.admin.cms.pages.manage')]
class PageBuilderController extends AbstractController
{
    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    #[Route('/{id}/preview', 'preview', methods: ['POST'])]
    public function preview(Page $page, Request $request): Response
    {
        $data = base64_decode($request->getContent());

        $template = $this->twig->createTemplate($data);
        $html = $template->render(['page' => $page]);

        return new Response(base64_encode($html));
    }
}
