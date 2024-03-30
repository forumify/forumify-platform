<?php

declare(strict_types=1);

namespace Forumify\Cms\Controller\Admin;

use Forumify\Cms\Entity\Page;
use Forumify\Cms\Form\PageType;
use Forumify\Cms\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('pages', 'page_')]
class PageController extends AbstractController
{
    public function __construct(
        private readonly PageRepository $pageRepository,
    ) {
    }

    #[Route('', 'list')]
    public function list(): Response
    {
        return $this->render('@Forumify/admin/cms/page/list.html.twig');
    }

    #[Route('/create', 'create')]
    public function create(Request $request): Response
    {
        return $this->handleForm($request, null);
    }

    #[Route('/{slug}', 'edit')]
    public function edit(Request $request, Page $page): Response
    {
        return $this->handleForm($request, $page);
    }

    #[Route('/{slug}/delete', 'delete')]
    public function delete(Page $page, Request $request): Response
    {
        if (!$request->get('confirmed')) {
            return $this->render('@Forumify/admin/cms/page/delete.html.twig', [
                'page' => $page,
            ]);
        }

        $this->pageRepository->remove($page);

        $this->addFlash('success', 'flashes.page_removed');
        return $this->redirectToRoute('forumify_admin_cms_page_list');
    }

    private function handleForm(Request $request, ?Page $page): Response
    {
        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $page = $form->getData();
            $this->pageRepository->save($page);

            $this->addFlash('success', 'flashes.page_saved');
            return $this->redirectToRoute('forumify_admin_cms_page_edit', [
                'slug' => $page->getSlug(),
            ]);
        }

        return $this->render('@Forumify/admin/cms/page/page.html.twig', [
            'form' => $form->createView(),
            'page' => $page,
        ]);
    }
}
