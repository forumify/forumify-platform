<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Page\Form\PageType;
use Forumify\Page\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageCreateController extends AbstractController
{
    #[Route('/page/create', 'page_create', priority: 1)]
    public function __invoke(Request $request, PageRepository $pageRepository): Response
    {
        $form = $this->createForm(PageType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $page = $form->getData();
            $pageRepository->save($page);
            return $this->redirectToRoute('forumify_admin_page', ['slug' => $page->getSlug()]);
        }

        return $this->render('@Forumify/admin/page/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
