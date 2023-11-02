<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Page\Entity\Page;
use Forumify\Page\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageDeleteController extends AbstractController
{
    #[Route('/page/{slug}/delete', 'page_delete')]
    public function __invoke(Request $request, PageRepository $pageRepository, Page $page): Response
    {
        if (!$request->get('confirmed')) {
            return $this->render('@Forumify/admin/page/delete.html.twig', [
                'page' => $page,
            ]);
        }

        $pageRepository->remove($page);
        $this->addFlash('success', 'flashes.page_removed');
        return $this->redirectToRoute('forumify_admin_page');
    }
}
